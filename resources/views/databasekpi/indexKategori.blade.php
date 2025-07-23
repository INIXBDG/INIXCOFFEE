@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
            <div class="text-end">
                <a href="{{ route('ketegori.kpi.create') }}" class="btn btn-primary">Buat Penilaian</a>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1 mb-5">Database Penilaian </h3>
                    <div class="container text-start w-100 d-flex justify-content-start">
                        <div class="row w-auto">
                            <div class="col-auto">
                                <div class="mt-1 mb-1">
                                    <div class="form-group">
                                        <label for="divisiSelectUtama">Pilih Divisi</label>
                                        <select name="divisiSelectUtama" id="divisiSelectUtama" class="form-control w-100">
                                            <option selected disabled>Semua Divisi</option>
                                            @foreach ($divisi as $item)
                                            <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="mt-1 mb-1">
                                    <div class="form-group">
                                        <label for="quartalSelectUtama">Pilih Quartal</label>
                                        <select name="quartalSelectUtama" id="quartalSelectUtama" class="form-control w-100">
                                            <option value="Q1">Q1</option>
                                            <option value="Q2">Q2</option>
                                            <option value="Q3">Q3</option>
                                            <option value="Q4">Q4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="mt-1 mb-1">
                                    <div class="form-group">
                                        <label for="tahunSelectUtama">Pilih Tahun</label>
                                        <select name="tahunSelectUtama" id="tahunSelectUtama" class="form-control w-100">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="table_karyawan" class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">No</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Nama Evaluator</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Nama Evaluated</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Divisi</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Taggal Pembuatan</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Quartal</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Tahun</th>
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_table" class="text-center">
                            <tr style="color: black;">
                                <td style="font-size: 14px; text-align: center;" colspan="8">Tidak Ada Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
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
<div class="modal fade" id="shareEvaluatorModal" tabindex="-1" aria-labelledby="shareEvaluatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm rounded">
            <form action="{{ route('penilaian.shareForm') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="shareEvaluatorModalLabel">Bagikan Form Penilaian</h5>
                    <button type="button" class="btn-close btn-close-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="modal-body-content" class="mb-3"></div>
                    <div id="content_select_input"></div>
                </div>

                <div class="modal-footer p-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-paper-plane me-1"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="reviewPenilaianModal" tabindex="-1" aria-labelledby="reviewPenilaianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm rounded">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewPenilaianModalLabel">Review Penilaian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div id="content-body-review"></div>
            </div>

            <div class="modal-footer p-3">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .select2-container {
        z-index: 9999 !important;
    }

    .select2-dropdown {
        z-index: 9999 !important;
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
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        const month = new Date().getMonth() + 1;

        let selectedQuarter = '';
        if (month >= 1 && month <= 3) {
            selectedQuarter = 'Q1';
        } else if (month >= 4 && month <= 6) {
            selectedQuarter = 'Q2';
        } else if (month >= 7 && month <= 9) {
            selectedQuarter = 'Q3';
        } else if (month >= 10 && month <= 12) {
            selectedQuarter = 'Q4';
        }

        $('#quartalSelectUtama').val(selectedQuarter);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const tahunSelect = document.getElementById('tahunSelectUtama');
        const tahunSekarang = new Date().getFullYear();
        const tahunAwal = 2020;

        for (let tahun = tahunAwal; tahun <= tahunSekarang; tahun++) {
            const option = document.createElement('option');
            option.value = tahun;
            option.text = tahun;
            if (tahun === tahunSekarang) {
                option.selected = true;
            }
            tahunSelect.appendChild(option);
        }
    });
</script>
<script>
    $('#quartalSelectUtama, #tahunSelectUtama, #divisiSelectUtama').on('change', function() {
        loadData();
    });

    $(document).ready(function() {
        loadData();
    });

    function loadData() {
        const selectedQuartal = $('#quartalSelectUtama').val();
        const selectedTahun = $('#tahunSelectUtama').val();
        const selectedDivisi = $('#divisiSelectUtama').val();

        $.ajax({
            url: "{{ route('penilaian.get.data') }}",
            type: 'get',
            data: {
                quartal: selectedQuartal,
                tahun: selectedTahun,
                divisi: selectedDivisi
            },
            beforeSend: function() {
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                let data = response.data;
                let tableBody = $('#tbody_table');
                tableBody.empty();

                if (data.length === 0) {
                    tableBody.append('<tr><td colspan="8" style="font-size: 14px;">Tidak Ada Data</td></tr>');
                    return;
                }

                let rowNumber = 1;

                data.forEach(function(item) {
                    let evaluatorName = item.evaluator || '-';
                    let evaluatedName = item.evaluated;
                    let tanggal = item.detail_kategori[0]?.isi_kriteria[0]?.tanggal || '-';
                    let quartal = item.quartal;
                    let tahun = item.tahun;

                    let totalSubCriteriaForThisAssessment = 0;
                    item.detail_kategori.forEach(function(detailKategori) {
                        totalSubCriteriaForThisAssessment += detailKategori.isi_kriteria.length;
                    });

                    let evaluatorHTML = '-';
                    if (Array.isArray(item.evaluator) && item.evaluator.length > 0) {
                        evaluatorHTML = item.evaluator.map(e => {
                            let style = e.is_red ? 'color: red;' : 'color: black;';
                            return `<span style="${style}">${e.name}</span>`;
                        }).join(', ');
                    }

                    let firstRow = true;

                    item.detail_kategori.forEach(function(detailKategori) {
                        detailKategori.isi_kriteria.forEach(function(isiKriteria, indexKriteria) {
                            let row = `<tr style="color: black;">`;

                            if (firstRow) {
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${rowNumber++}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${evaluatorHTML}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${evaluatedName}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${item.evaluatedDivisi}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${tanggal}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${quartal}</td>`;
                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${tahun}</td>`;

                                row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" onclick="shareForm(this)" data-bs-toggle="modal" data-bs-target="#shareEvaluatorModal">
                                                <i class="fa-solid fa-paper-plane me-4"></i> Share
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" onclick="ReviewForm(this)" data-bs-toggle="modal" data-bs-target="#reviewPenilaianModal">
                                                <i class="fa-solid fa-list-check me-4"></i> Review
                                            </a>
                                        </li>
                                        <li>
                                            <a href="/penilaian/detail/data-penilaian/${item.kode_form}/${item.id_karyawan}" class="dropdown-item">
                                                <i class="fa-solid fa-magnifying-glass me-4"></i> Detail
                                            </a>
                                        </li>`;

                                if (evaluatorName !== '-') {
                                    row += `
                                        <li>
                                            <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" onclick="ReviewForm(this)" data-bs-toggle="modal" data-bs-target="#reviewPenilaianModal">
                                                <i class="fa-solid fa-brush me-4"></i> Bersihkan
                                            </a>
                                        </li>`;
                                }

                                row += `
                                        </ul>
                                    </div>
                                </td>`;
                                firstRow = false;
                            }

                            row += `</tr>`;
                            tableBody.append(row);
                        });
                    });
                });

                if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                    $('#table_karyawan').DataTable().destroy();
                }

                $('#table_karyawan').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data: ", error);
                let tableBody = $('#tbody_table');
                tableBody.empty();
                tableBody.append('<tr><td colspan="5" style="font-size: 14px; color: red;">Gagal memuat data. Silakan coba lagi.</td></tr>');
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    }

    let allKaryawan = [];

    function ReviewForm(el) {
        const kodeForm = el.dataset.kode;
        const idKaryawan = el.dataset.id;

        const modalBody = $('#content-body-review');
        modalBody.html(`<p class="mb-3">Pilih Evaluator Yang Akan Direview</p>`);

        $.ajax({
            url: "{{ route('penilaian.get.data') }}",
            type: 'get',
            success: function(response) {
                console.log("Response:", response);
                const data = response.data;

                const formItem = data.find(item =>
                    item.kode_form == kodeForm && item.id_karyawan == idKaryawan
                );

                const modalBody = $('#content-body-review');
                modalBody.html(`<p class="mb-3">Pilih Evaluator Yang Akan Direview</p>`);

                if (!formItem || !formItem.evaluator || !formItem.evaluator.length) {
                    modalBody.append('<p class="text-danger">Tidak ada evaluator untuk form ini.</p>');
                    return;
                }

                formItem.evaluator.forEach((evaluator, index) => {
                    const evaluatorId = evaluator.id;
                    const evaluatorName = evaluator.name;
                    const isRed = evaluator.is_red;

                    const button = `
                        <a href="/reviewPenilaian/${kodeForm}/${evaluatorId}" 
                        class="btn mb-2 ms-3 ${isRed ? 'btn-danger' : 'btn-secondary'}">
                            Review Hasil Penilaian ${evaluatorName}
                        </a>
                    `;
                    modalBody.append(button);
                });
            },
            error: function() {
                modalBody.html('<p class="text-danger">Gagal mengambil data evaluator.</p>');
            }
        });

        $('#reviewPenilaianModal').modal('show');
    }

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
            type: 'get',
            success: function(response) {
                const karyawan = response.karyawan;
                allKaryawan = karyawan;

                const divisiSet = new Set();
                karyawan.forEach(item => {
                    if (item.divisi) divisiSet.add(item.divisi);
                });

                let optionsDivisi = '';
                divisiSet.forEach(divisi => {
                    optionsDivisi += `<option value="${divisi}">${divisi}</option>`;
                });

                if (!karyawan.length) {
                    contentSelect.append('<p style="font-size: 14px;">Data Tidak Ditemukan</p>');
                    return;
                }

                let $divisiSelectUtama = $('#divisiSelectUtama');
                $divisiSelectUtama.empty();
                $divisiSelectUtama.append('<option disabled selected>Pilih Divisi</option>');
                divisiSet.forEach(divisi => {
                    $divisiSelectUtama.append(`<option value="${divisi}">${divisi}</option>`);
                });

                const html = `
                    <label for="divisi">Pilih Divisi</label>
                    <select id="multiple-select-field-divisi" name="divisi[]" multiple class="form-select">
                        ${optionsDivisi}
                    </select>

                    <label for="evaluator" class="mt-4">Pilih Evaluator</label>
                    <select id="multiple-select-field-karyawan" name="id_karyawan[]" multiple class="form-select">
                    </select>

                    <label for="evaluator" class="mt-4">Jenis Penilaian</label>
                    <select name="jenis_penilaian" class="form-select" Required>
                        <option selected disabled>Pilih Jenis Penilaian</option>
                        <option value="General Manager">General Manager</option>
                        <option value="Manager/SPV/Team Leader (Atasan Langsung)">Manager/SPV/Team Leader (Atasan Langsung)</option>
                        <option value="Rekan Kerja (Satu Divisi)">Rekan Kerja (Satu Divisi)</option>
                        <option value="Pekerja (Beda Divisi)">Pekerja (Beda Divisi)</option>
                        <option value="Self Apprisial">Self Apprisial</option>
                    </select>
                `;

                contentSelect.append(html);

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

                const gmList = karyawan.filter(item =>
                    item.jabatan === 'GM' &&
                    item.divisi === 'Sales & Marketing'
                );

                function updateEvaluatorOptions(selectedDivisi) {
                    let filteredKaryawan = karyawan.filter(item =>
                        selectedDivisi.includes(item.divisi)
                    );

                    gmList.forEach(gm => {
                        if (!filteredKaryawan.find(k => k.id === gm.id)) {
                            filteredKaryawan.push(gm);
                        }
                    });

                    gmList.forEach(gm => {
                        const option = $evaluatorSelect.find(`option[value="${gm.id}"]`);
                        option.prop('disabled', true);
                    });

                    modalBody.find('input[name="id_karyawan[]"][data-gm="true"]').remove();
                    gmList.forEach(gm => {
                        const gmHiddenInput = `<input type="hidden" name="id_karyawan[]" value="${gm.id}" data-gm="true">`;
                        modalBody.append(gmHiddenInput);
                    });

                    let options = '';
                    filteredKaryawan.forEach(item => {
                        const isGMFromSales = gmList.find(gm => gm.id === item.id);
                        options += `<option value="${item.id}" ${isGMFromSales ? 'selected disabled' : ''}>
                        ${item.nama_lengkap} - ${item.divisi}
                    </option>`;
                    });

                    $evaluatorSelect.html(options).trigger('change');
                }

                $divisiSelect.on('change', function() {
                    const selectedDivisi = $(this).val();
                    updateEvaluatorOptions(selectedDivisi);
                });

                updateEvaluatorOptions([]);
            }
        });

        $('#shareEvaluatorModal').modal('show');
    }
</script>
@endpush
@endsection