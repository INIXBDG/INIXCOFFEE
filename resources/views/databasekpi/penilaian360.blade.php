@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered justify-content-center">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-start mb-2">
        <div class="btn-group flex-wrap" role="group" id="groupButtonJenisPenilaian" aria-label="Button group">
        </div>
    </div>
    <div class="d-flex justify-content-start mb-3">
        <div class="btn-group" id="groupButtonEvaluator" role="group" aria-label="Vertical button group">
        </div>
    </div>
    <div class="container text-center">
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-center mb-3">
                    <div class="shadow-card p-5 border-0 w-100">
                        <div id="formContainer" class="w-100">
                            <form class="w-100 text-start">
                                <div class="text-center text-danger">...memuat</div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>Telat</td>
                                    <td>Sakit</td>
                                    <td>Izin</td>
                                </tr>
                            </thead>
                            <tbody id="content_body_absen"></tbody>
                            <tfoot id="content_footer_absen"></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

<style>
    body {
        background: linear-gradient(to right, #f4f6f9, #ffffff);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h4,
    h5 {
        font-weight: 600;
    }

    label.form-label {
        font-weight: 500;
        color: #495057;
    }

    textarea[readonly],
    input[readonly] {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #343a40;
    }

    .auto-resize-limited {
        resize: none;
        overflow-y: auto;
        max-height: 180px;
    }

    #formContainer h5 {
        border-left: 4px solid #0d6efd;
        padding-left: 0.75rem;
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #0d6efd;
    }

    .shadow-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    }

    .btn-outline-secondary {
        min-width: 180px;
        text-align: left;
    }

    .btn-outline-secondary.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .btn-outline-secondary:hover {
        background-color: #e2e6ea;
        color: #212529;
    }

    .evaluator-btn,
    .jenis-btn {
        font-size: 14px;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: 8px;
    }

    .cube {
        width: 50px;
        height: 50px;
        margin: auto;
        display: flex;
        flex-wrap: wrap;
        animation: rotate 1s linear infinite;
    }

    .cube_item {
        width: 20px;
        height: 20px;
        margin: 2px;
        background-color: #007bff;
        border-radius: 4px;
    }

    @keyframes rotate {
        0% {
            transform: rotate(0);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    let globalData = [];
    let selectedJenis = null;
    let selectedEvaluator = null;
    let evaluatedName = '';

    function renderForm(jenis, evaluatorName) {
        const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
        if (!jenisData) return;

        const evaluatorData = jenisData.evaluator.find(e => e.nama_evaluator === evaluatorName);
        if (!evaluatorData) return;

        let html = `
        <form class="w-100 text-start">
            <div class="mb-5 text-center">
                <h4 class="fw-semibold text-secondary">Penilaian ${jenis}</h4>
                <span class="text-dark fw-bold">${evaluatedName}</span>
            </div>
            <div style="height: auto;">
    `;

        evaluatorData.kriteria.forEach(function(k) {
            html += `<h5 class="fw-semibold text-primary border-start border-4 ps-2 mb-3">${k.kriteria}</h5>`;

            k.subKriteria.forEach(function(sk) {
                const nilai = sk.nilai ?? '';
                const rawPesan = sk.deskripsi ?? '';
                const tipe = sk.tipe ?? '';

                const pesan = (rawPesan === 'null' || rawPesan == null) ? '' : rawPesan;
                const isAngka = !isNaN(pesan) && pesan !== '';

                html += `<div class="mb-4">`;
                html += `<label class="form-label text-secondary">${sk.subKriteria}</label>`;

                // === LOGIKA TAMPILAN ===
                if (tipe === 'textarea') {
                    html += `
                <textarea 
                    class="form-control shadow-sm auto-resize-limited" 
                    readonly>${pesan}</textarea>`;
                } else if (isAngka || pesan === '') {
                    html += `<input type="number" class="form-control shadow-sm" readonly value="${nilai}">`;
                } else {
                    html += `<input type="number" class="form-control shadow-sm" readonly value="${nilai}">`;
                    html += `
                <textarea 
                    class="form-control shadow-sm mt-2 auto-resize-limited" 
                    readonly>${pesan}</textarea>`;
                }

                html += `</div>`;
            });
        });

        html += `
        </div>
    </form>`;
        $('#formContainer').html(html);

        $('.auto-resize-limited').each(function() {
            this.style.height = 'auto';
            this.style.maxHeight = '180px';
            this.style.overflowY = 'auto';
            this.style.height = Math.min(this.scrollHeight, 180) + 'px';
        });
    }

    function renderEvaluators(jenis) {
        const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
        if (!jenisData) return;

        const evaluators = jenisData.evaluator;
        const container = $('#groupButtonEvaluator');
        container.empty();

        let counter = 1;

        evaluators.forEach(function(ev) {
            const label = `Evaluator ${counter++}`;
            container.append(`
                <button type="button" class="btn btn-outline-secondary mb-2 evaluator-btn" data-nama="${ev.nama_evaluator}">
                    ${label}
                </button>
            `);
        });

        $('.evaluator-btn').on('click', function() {
            $('.evaluator-btn').removeClass('active');
            $(this).addClass('active');

            selectedEvaluator = $(this).data('nama');
            renderForm(selectedJenis, selectedEvaluator);
        });

        if (evaluators.length > 0) {
            selectedEvaluator = evaluators[0].nama_evaluator;
            $(`.evaluator-btn[data-nama="${selectedEvaluator}"]`).addClass('active');
            renderForm(selectedJenis, selectedEvaluator);
        }
    }

    function renderJenisPenilaian(data) {
        const container = $('#groupButtonJenisPenilaian');
        container.empty();

        data.forEach(function(item) {
            container.append(`
                <button type="button" class="btn btn-outline-secondary me-2 mb-2 jenis-btn" data-jenis="${item.jenis_penilaian}">
                    ${item.jenis_penilaian}
                </button>
            `);
        });

        $('.jenis-btn').on('click', function() {
            $('.jenis-btn').removeClass('active');
            $(this).addClass('active');

            selectedJenis = $(this).data('jenis');
            renderEvaluators(selectedJenis);
        });

        if (data.length > 0) {
            selectedJenis = data[0].jenis_penilaian;
            $(`.jenis-btn[data-jenis="${selectedJenis}"]`).addClass('active');
            renderEvaluators(selectedJenis);
        }
    }

    function loadData() {
        $.ajax({
            url: `/penilaian360/get/{{ $id_karyawan }}`,
            type: 'get',
            success: function(response) {
                if (response.message) {
                    $('#formContainer').html(`<div class="text-center text-danger">${response.message}</div>`);
                    return;
                }
                dataAbsen = response.dataAbsen;
                let content_body_absen = $('#content_body_absen');
                content_body_absen.empty();

                content_body_absen.append(`
                    <tr>
                        <td>${dataAbsen.telat}</td>
                        <td>${dataAbsen.sakit}</td>
                        <td>${dataAbsen.izin}</td>
                    </tr>
                `);

                let content_footer_absen = $('#content_footer_absen');
                content_footer_absen.empty();

                content_footer_absen.append(`
                    <tr>
                        <td colspan="3">Data Absen ${response.quartal} tahun ${response.tahun}</td>
                    </tr>
                    <tr>
                        <td style="max-width: 400px; word-wrap: break-word; white-space: normal;" class="text-start" colspan="3">
                            <strong>Catatan : </strong>
                            <br/>
                           ${response.catatan === null | response.catatan === 'null' ? 'belum ada catatan!' : response.catatan.join('<br>')}
                        </td>
                    <tr>
                `);

                globalData = response.data;
                evaluatedName = response.nama_evaluated[0] ?? '-';
                renderJenisPenilaian(globalData);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    $(document).ready(function() {
        loadData();
    });
</script>
@endpush