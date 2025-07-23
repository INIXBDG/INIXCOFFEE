@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container-fluid mb-5 mt-4">
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
    <a href="{{ route('ketegoriKPI.get') }}" class="btn click-primary mb-4">Kembali</a>
    <div class="text-center mb-3">
        <h3>Detail Penilaian</h3>
    </div>
    @if ($kodeForm)
    <input type="hidden" id="kodeForm" name="kodeForm" value="{{ $kodeForm }}">
    @endif
    @if ($id_karyawan)
    <input type="hidden" id="id_karyawan" name="id_karyawan" value="{{ $id_karyawan }}">
    @endif
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-sm-4 mx-auto">
                <div class="card p-4 ms-4 mb-4">
                    <div class="text-end" id="content_utama">
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="card p-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Kriteria</th>
                                <th scope="col">Sub Kriteria</th>
                                <th scope="col">Bobot</th>
                                <th scope="col">Nilai</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody id="body_content">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container">
            <select id="selectTahun" name="tahun" class="form-control w-25 mt-5 p-2" style="margin-bottom: -40px; margin-top: 40px;">
            </select>
            <div class="row">
                <div class="col-sm">
                    <div class="text-center mt-5 mb-3">
                        <h4>Trend Line Tahun Ini</h4>
                    </div>

                    <div class="card flex-fill w-100 draggable p-2">
                        <div class="card-body py-3">
                            <div class="chart chart-sm">
                                <div class="chartjs-size-monitor">
                                    <div class="chartjs-size-monitor-expand">
                                        <div class=""></div>
                                    </div>
                                    <div class="chartjs-size-monitor-shrink">
                                        <div class=""></div>
                                    </div>
                                </div>
                                <canvas id="chartjs-dashboard-line chart-bar" style="display: block; height: 252px; width: 100%;" width="856" height="504" class="chart-bar chartjs-render-monitor"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="text-center mt-5 mb-3">
                        <h4>Trend Line Progress</h4>
                    </div>
                    <div class="card flex-fill w-100 draggable p-2">
                        <div class="card-body py-3">
                            <div class="chart chart-sm">
                                <div class="chartjs-size-monitor">
                                    <div class="chartjs-size-monitor-expand">
                                        <div class=""></div>
                                    </div>
                                    <div class="chartjs-size-monitor-shrink">
                                        <div class=""></div>
                                    </div>
                                </div>
                                <canvas id="chartjs-dashboard-line chart-line" style="display: block; height: 252px; width: 100%;" width="856" height="504" class="chart-line chartjs-render-monitor"></canvas>
                            </div>
                        </div>
                    </div>
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
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        loadData();
    });

    function loadData() {
        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('kodeForm', $('#kodeForm').val());
        formData.append('id_karyawan', $('#id_karyawan').val());
        formData.append('tahun', $('#selectTahun').val());

        $.ajax({
            url: "{{ route('penilaian.detail.get') }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                let content = $('#body_content');
                content.empty();

                let content_utama = $('#content_utama');
                content_utama.empty();

                const data = response.data[0];
                const evaluators = data.data.evaluator;
                const kriteriaData = data.data.dataKriteria;
                const evaluated = data.evaluated;

                let listEvaluatorHTML = evaluators.map(ev => `<li class="list-group-item">${ev.nama}</li>`).join('');
                let jenisPenilaian = evaluators.length > 0 ? evaluators[0].jenis_penilaian : '-';

                content_utama.append(`
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Evaluator</label>
                        <ul class="list-group ms-2">${listEvaluatorHTML}</ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Evaluated</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${evaluated.nama}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Jenis Penilaian</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${jenisPenilaian}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Quartal</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${evaluated.quartal}</li></ul>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="mb-2">Tahun</label>
                        <ul class="list-group ms-2"><li class="list-group-item">${evaluated.tahun}</li></ul>
                    </div>
                `);

                if (kriteriaData.length === 0) {
                    content.append(`<tr><td colspan="5" class="text-center">Tidak Ada Kriteria</td></tr>`);
                    return;
                }

                const persentaseJenis = {
                    'General Manager': 35,
                    'Manager/SPV/Team Leader (Atasan Langsung)': 30,
                    'Rekan Kerja (Satu Divisi)': 16,
                    'Pekerja (Beda Divisi)': 10,
                    'Self Apprisial': 5
                };

                let groupSkor = {
                    'General Manager': [],
                    'Manager/SPV/Team Leader (Atasan Langsung)': [],
                    'Rekan Kerja (Satu Divisi)': [],
                    'Pekerja (Beda Divisi)': [],
                    'Self Apprisial': []
                };

                let dataTotalPerTahun = {};
                let tahunData = evaluated.tahun;

                evaluators.forEach((evaluator) => {
                    content.append(`
                        <tr>
                            <td colspan="5" class="bg-light fw-bold">
                                ${evaluator.nama} - ${evaluator.jenis_penilaian}
                            </td>
                        </tr>
                    `);

                    let nilaiList = evaluator.nilai;
                    let nilaiIndex = 0;
                    let totalSkorEvaluator = 0;

                    kriteriaData.forEach(kriteria => {
                        const subKriteriaList = kriteria.detailKriteria;
                        const rowspan = subKriteriaList.length;

                        subKriteriaList.forEach((sub, idxSub) => {
                            const nilaiItem = nilaiList[nilaiIndex++] || {
                                nilai: '-'
                            };
                            const nilai = nilaiItem.nilai;
                            const nilaiAngka = isNaN(parseFloat(nilai)) ? 0 : parseFloat(nilai);
                            const bobot = parseFloat(sub.bobot);
                            const skor = (nilaiAngka * bobot) / 100;

                            totalSkorEvaluator += skor;

                            content.append(`
                                <tr>
                                    ${idxSub === 0 ? `<td rowspan="${rowspan}">${kriteria.kriteria}</td>` : ''}
                                    <td class="text-start">${sub.sub_kriteria}</td>
                                    <td>${bobot} %</td>
                                    <td>${nilai}</td>
                                    <td>${skor.toFixed(2)}</td>
                                </tr>
                            `);
                        });
                    });

                    if (groupSkor[evaluator.jenis_penilaian]) {
                        groupSkor[evaluator.jenis_penilaian].push(totalSkorEvaluator);
                    }

                    content.append(`
                        <tr class="fw-bold text-center text-white" style="background: #7F8CAA">
                            <td colspan="4" class="text-center">Total (${evaluator.nama})</td>
                            <td>${totalSkorEvaluator.toFixed(2)}</td>
                        </tr>
                    `);
                });

                let totalSemuaSkor = 0;

                for (const [jenis, skorList] of Object.entries(groupSkor)) {
                    if (skorList.length > 0) {
                        const total = skorList.reduce((a, b) => a + b, 0);
                        const rata2 = total / skorList.length;
                        const persen = persentaseJenis[jenis] ?? 0;
                        const skorFinal = (rata2 * persen) / 100;
                        totalSemuaSkor += skorFinal;
                    }
                }

                content.append(`
                    <tr class="fw-bold text-white" style="background: #393E46">
                        <td colspan="4" class="text-end">Total Semua Nilai</td>
                        <td>${totalSemuaSkor.toFixed(2)}</td>
                    </tr>
                `);

                if (!dataTotalPerTahun[tahunData]) {
                    dataTotalPerTahun[tahunData] = 0;
                }
                dataTotalPerTahun[tahunData] = totalSemuaSkor;

                tampilkanChartTahunIni($('#selectTahun').val(), totalSemuaSkor);
                tampilkanChartSemuaTahun(dataTotalPerTahun);
            }
        });
    }
</script>
<script>
    let chartTahunIni = null;
    let chartAllYears = null;

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
        loadData();
    });

    function tampilkanChartTahunIni(tahun, total) {
        const ctx = document.querySelector(".chart-bar").getContext("2d");

        if (chartTahunIni) {
            chartTahunIni.destroy();
        }

        chartTahunIni = new Chart(ctx, {
            type: "bar",
            data: {
                labels: [tahun],
                datasets: [{
                    label: "Total Skor Tahun Ini",
                    data: [total],
                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 10
                        }
                    }]
                }
            }
        });
    }

    function tampilkanChartSemuaTahun(dataTotalPerTahun) {
        const ctx = document.querySelector(".chart-line").getContext("2d");

        const labels = Object.keys(dataTotalPerTahun);
        const data = Object.values(dataTotalPerTahun);

        if (chartAllYears) {
            chartAllYears.destroy();
        }

        chartAllYears = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Total Nilai per Tahun",
                    data: data,
                    backgroundColor: "rgba(153, 102, 255, 0.4)",
                    borderColor: "rgba(153, 102, 255, 1)",
                    fill: false
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 10
                        }
                    }]
                }
            }
        });
    }
</script>

@endpush
@endsection