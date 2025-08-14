@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container-fluid mb-3">
    <!-- <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div> -->

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="text-end">
                <a href="{{ route('ketegori.kpi.create') }}" class="btn text-white cl-blue">Buat Penilaian</a>
            </div>
            <div class="card mt-2">
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
                                <th rowspan="2" style="font-size: 14px; text-align: center;">Yang Dinilai</th>
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

<div class="modal fade" id="shareEvaluatorModal" tabindex="-1" aria-labelledby="shareEvaluatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm rounded">
            <form action="{{ route('penilaian.shareForm') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="shareEvaluatorModalLabel">Bagikan Formulir Penilaian</h5>
                    <button type="button" class="btn-close text-white cl-red btn" data-bs-dismiss="modal" aria-label="Tutup">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div id="modal-body-content"></div>
                    <div id="content_select_input"></div>
                </div>
                <div class="modal-footer p-3">
                    <button type="button" class="btn text-white cl-red btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn cl-green text-white btn-sm">
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
                <button type="button" class="btn-close btn-close-white btn" data-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="modal-body p-4">
                <div id="content-body-review"></div>
            </div>

            <div class="modal-footer p-3">
                <button type="button" class="btn text-white cl-red btn-sm" data-dismiss="modal">
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
@endsection
@section('script')
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
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

                if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                    var table = $('#table_karyawan').DataTable();
                    var currentPage = table.page();
                    table.destroy();
                } else {
                    var currentPage = 0;
                }

                if (data.length === 0) {
                    tableBody.append('<tr><td colspan="8" style="font-size: 14px;">Tidak Ada Data</td></tr>');
                } else {
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

                        let evaluatorHTML = generateEvaluatorByPenilaian(item);

                        let firstRow = true;

                        item.detail_kategori.forEach(function(detailKategori) {
                            detailKategori.isi_kriteria.forEach(function(isiKriteria, indexKriteria) {
                                let row = `<tr style="color: black;">`;

                                if (firstRow) {
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${rowNumber++}</td>`;
                                    row += `<td style="font-size: 14px; text-align: left;" class="text-start" rowspan="${totalSubCriteriaForThisAssessment}">${evaluatorHTML}</td>`;
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${evaluatedName}</td>`;
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${item.evaluatedDivisi}</td>`;
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${tanggal}</td>`;
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${quartal}</td>`;
                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">${tahun}</td>`;

                                    row += `<td style="font-size: 14px;" rowspan="${totalSubCriteriaForThisAssessment}">
                                        <div class="dropdown">
                                            <button class="btn cl-grey text-white dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Action
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" onclick="shareForm(this)" data-toggle="modal" data-target="#shareEvaluatorModal">
                                                    <i class="fa-solid fa-paper-plane me-4"></i> Share
                                                </a>
                                                <a href="#" class="dropdown-item" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" data-jenis_penilaian="${item.jenis_penilaian}" onclick="ReviewForm(this)" data-toggle="modal" data-target="#reviewPenilaianModal">
                                                    <i class="fa-solid fa-list-check me-4"></i> Review
                                                </a>
                                                <a href="/penilaian/detail/data-penilaian/${item.kode_form}/${item.id_karyawan}" class="dropdown-item">
                                                    <i class="fa-solid fa-magnifying-glass me-4"></i> Detail
                                                </a>`;

                                    if (evaluatorName !== '-') {
                                        row += `
                                        <a href="javascript:void(0)" class="dropdown-item btn-clean" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}" 
                                        data-jenis_penilaian="${item.jenis_penilaian}" data-quartal="${quartal}" data-tahun="${tahun}">
                                            <i class="fa-solid fa-brush me-4"></i> Bersihkan
                                        </a>`;
                                    }

                                    row += `
                                            </div>
                                        </div>
                                    </td>`;
                                    firstRow = false;
                                }

                                row += `</tr>`;
                                tableBody.append(row);
                            });
                        });
                    });
                }

                var table = $('#table_karyawan').DataTable({
                    "paging": true,
                    "pageLength": 10,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "lengthChange": false,
                    "language": {
                        "emptyTable": "Tidak Ada Data"
                    }
                });

                table.page(currentPage).draw(false);
            },
            error: function(xhr, status, error) {
                let tableBody = $('#tbody_table');
                tableBody.empty();
                tableBody.append('<tr><td colspan="8" style="font-size: 14px; color: red;">Gagal memuat data. Silakan coba lagi.</td></tr>');
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    }

    $(document).on('click', '.btn-clean', function(e) {
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
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, bersihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/penilaian/clean`,
                    type: 'POST',
                    data: formData,
                    processData: false, // penting
                    contentType: false, // penting
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dibersihkan.'
                        });
                        loadData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
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
                let style = e.is_red ? 'color: red;' : 'color: black;';
                let nameParts = e.name.split(" ");
                let limitedName = nameParts.slice(0, 2).join(" ");
                return `<span style="${style} ms-3">${limitedName}</span>`;
            }).join(', ');

            evaluatorGroupedHTML += `${namesHTML}<br/><br/>`;
        });

        return evaluatorGroupedHTML;
    }

    let allKaryawan = [];

    function ReviewForm(el) {
        const kodeForm = el.dataset.kode;
        const idKaryawan = el.dataset.id;
        const jenisPenilaian = el.dataset.jenis_penilaian;

        const modalBody = $('#content-body-review');
        modalBody.html(`<p class="mb-3">Pilih Evaluator Yang Akan Direview</p>`);

        $.ajax({
            url: "{{ route('penilaian.get.data') }}",
            type: 'get',
            success: function(response) {
                const data = response.data;

                const formItem = data.find(item =>
                    item.kode_form == kodeForm && item.id_karyawan == idKaryawan
                );

                modalBody.html(`<p class="mb-3">Pilih Evaluator Yang Akan Direview</p>`);

                if (!formItem || !formItem.evaluator_by_jenis) {
                    modalBody.append('<p class="w-red">Tidak ada evaluator untuk form ini.</p>');
                    return;
                }

                const evaluatorByJenis = formItem.evaluator_by_jenis;

                Object.entries(evaluatorByJenis).forEach(([jenis, evaluators]) => {
                    evaluators.forEach(evaluator => {
                        const evaluatorId = evaluator.id;
                        const isRed = evaluator.is_red;

                        // Batasi nama maksimal 2 kata pertama
                        const nameParts = evaluator.name.split(" ");
                        const limitedName = nameParts.slice(0, 2).join(" ");

                        const button = `
                        <a href="/reviewPenilaian/${kodeForm}/${evaluatorId}/${encodeURIComponent(jenis)}/${idKaryawan}" 
                        class="btn mb-2 ms-3 ${isRed ? 'text-white cl-red' : 'text-white cl-grey'}">
                            ${limitedName} – ${jenis}
                        </a>
                    `;

                        modalBody.append(button);
                    });
                });
            },
            error: function() {
                modalBody.html('<p class="w-red">Gagal mengambil data evaluator.</p>');
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
            type: 'GET',
            success: function(response) {
                const karyawan = response.karyawan;
                const divisiSet = new Set(karyawan.map(item => item.divisi).filter(Boolean));
                const gmList = karyawan.filter(item => item.jabatan === 'GM' && item.divisi === 'Sales & Marketing');

                const html = `
                <label class="mt-4">Jenis Penilaian</label>
                <select name="jenis_penilaian" class="form-control" required>
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
                    const allowGm = jenis === 'General Manager' || jenis === 'Manager/SPV/Team Leader (Atasan Langsung)';
                    renderDivisiSelect(allowGm);
                    const selectedDivisi = $divisiSelect.val() || [];
                    const finalDivisi = allowGm ? [...new Set([...selectedDivisi, 'Sales & Marketing'])] : selectedDivisi;
                    updateEvaluatorOptions(finalDivisi, allowGm);
                });

                $divisiSelect.on('change', function() {
                    const selectedDivisi = $(this).val() || [];
                    const jenis = $jenisPenilaianSelect.val();
                    const allowGm = jenis === 'General Manager' || jenis === 'Manager/SPV/Team Leader (Atasan Langsung)';
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