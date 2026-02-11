@extends('databasekpi.berandaKPI')

@section('contentKPI')
<div class="content-wrapper">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="loading-spinner"></div>
        </div>
    </div>

    <div class="d-flex justify-content-start mb-2">
        <div class="btn-group flex-wrap" role="group" id="groupButtonJenisPenilaian"></div>
    </div>

    <div class="d-flex justify-content-start mb-3">
        <div class="btn-group" id="groupButtonEvaluator" role="group"></div>
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
                            <tbody id="content_body_absen">
                                <tr>
                                    <td colspan="3">Tidak ada Data...</td>
                                </tr>
                            </tbody>
                            <tfoot id="content_footer_absen">
                                <tr>
                                    <td colspan="3">Data Absen quartal tahun ...</td>
                                </tr>
                                <tr>
                                    <td class="text-start" colspan="3">
                                        <strong>Catatan :</strong><br>
                                        Belum ada catatan mengenai anda
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    background: linear-gradient(to right, #f4f6f9, #ffffff);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.shadow-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05);
}
.btn-outline-secondary.active {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
.auto-resize-limited {
    resize: none;
    overflow-y: auto;
    max-height: 180px;
}
</style>

<script>
let globalData = [];
let selectedJenis = null;
let selectedEvaluator = null;
let evaluatedName = '';

$(function () {
    loadData();
});

function loadData() {
    $.ajax({
        url: `/penilaian360/get/{{ $id_karyawan }}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response || response.message) {
                $('#formContainer').html(`<div class="text-danger text-center">${response.message ?? 'Data kosong'}</div>`);
                return;
            }

            renderAbsen(response);
            globalData = response.data ?? [];
            evaluatedName = response.nama_evaluated?.[0] ?? '-';

            if (globalData.length > 0) {
                renderJenisPenilaian(globalData);
            }
        },
        error: function (xhr) {
            $('#formContainer').html(`<div class="text-danger text-center">Gagal memuat data</div>`);
            console.error(xhr.responseText);
        }
    });
}

function renderAbsen(response) {
    const dataAbsen = response.dataAbsen ?? {};
    $('#content_body_absen').html(`
        <tr>
            <td>${dataAbsen.telat ?? 0}</td>
            <td>${dataAbsen.sakit ?? 0}</td>
            <td>${dataAbsen.izin ?? 0}</td>
        </tr>
    `);

    let catatan = 'belum ada catatan!';
    if (Array.isArray(response.catatan)) {
        catatan = response.catatan.join('<br>');
    } else if (response.catatan && response.catatan !== 'null') {
        catatan = response.catatan;
    }

    $('#content_footer_absen').html(`
        <tr>
            <td colspan="3">Data Absen ${response.quartal} tahun ${response.tahun}</td>
        </tr>
        <tr>
            <td class="text-start" colspan="3">
                <strong>Catatan :</strong><br>${catatan}
            </td>
        </tr>
    `);
}

function renderJenisPenilaian(data) {
    const container = $('#groupButtonJenisPenilaian');
    container.empty();

    data.forEach(item => {
        container.append(`
            <button class="btn btn-outline-secondary me-2 mb-2 jenis-btn" data-jenis="${item.jenis_penilaian}">
                ${item.jenis_penilaian}
            </button>
        `);
    });

    $('.jenis-btn').on('click', function () {
        $('.jenis-btn').removeClass('active');
        $(this).addClass('active');
        selectedJenis = $(this).data('jenis');
        renderEvaluators(selectedJenis);
    });

    $('.jenis-btn').first().click();
}

function renderEvaluators(jenis) {
    const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
    if (!jenisData) return;

    const container = $('#groupButtonEvaluator');
    container.empty();

    jenisData.evaluator.forEach((ev, i) => {
        container.append(`
            <button class="btn btn-outline-secondary mb-2 evaluator-btn" data-nama="${ev.nama_evaluator}">
                Evaluator ${i + 1}
            </button>
        `);
    });

    $('.evaluator-btn').on('click', function () {
        $('.evaluator-btn').removeClass('active');
        $(this).addClass('active');
        selectedEvaluator = $(this).data('nama');
        renderForm(jenis, selectedEvaluator);
    });

    $('.evaluator-btn').first().click();
}

function renderForm(jenis, evaluatorName) {
    const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
    const evaluatorData = jenisData?.evaluator.find(e => e.nama_evaluator === evaluatorName);
    if (!evaluatorData) return;

    let html = `
        <form class="text-start">
            <div class="mb-4 text-center">
                <h4>Penilaian ${jenis}</h4>
                <strong>${evaluatedName}</strong>
            </div>
    `;

    evaluatorData.kriteria.forEach(k => {
        html += `<h5>${k.kriteria}</h5>`;
        k.subKriteria.forEach(sk => {
            html += `
                <div class="mb-3">
                    <label>${sk.subKriteria}</label>
                    <input class="form-control mb-2" readonly value="${sk.nilai ?? ''}">
                    ${sk.deskripsi && sk.deskripsi !== 'null'
                        ? `<textarea class="form-control auto-resize-limited" readonly>${sk.deskripsi}</textarea>`
                        : ''}
                </div>
            `;
        });
    });

    html += `</form>`;
    $('#formContainer').html(html);
}
</script>
@endsection
