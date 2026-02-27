@extends('layouts_crm.app')

@section('crm_contents')
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
    <div class="modal fade" id="detailPesertaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
               <table id="tablePeserta"
                class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Usia</th>
                            <th>Perusahaan</th>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody id="detailPesertaBody"></tbody>
                </table>

            </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ url('import-klien') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data Klien</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xlsx / .csv)</label>
                            <input type="file" name="file" class="form-control" id="file" required>
                            <div class="form-text">
                                Unduh <a href="{{ route('excel.dbklien') }}">Template Excel</a> untuk memastikan format data benar.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Mulai Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row justify-content-center m-1">
        <div class="card h-100 shadow-sm border-0 rounded-4"><div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0 text-primary">Database Klien</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">Import Database Klien</button>
            </div>
            <div class="card-body p-3">
                <!-- From Uiverse.io by JesusRafaelNavaCruz --> 
                <div class="container-dash"
                id="cardPesertaTahun">
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center m-1">
        <div class="card h-100 shadow-sm border-0 rounded-3">
            <div class="card-body p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-usia-tab" data-bs-toggle="tab" data-bs-target="#nav-usia" type="button" role="tab" aria-controls="nav-usia" aria-selected="true">Usia</button>
                        <button class="nav-link" id="nav-lokasi-tab" data-bs-toggle="tab" data-bs-target="#nav-lokasi" type="button" role="tab" aria-controls="nav-lokasi" aria-selected="false">Lokasi</button>
                        <button class="nav-link" id="nav-materi-tab" data-bs-toggle="tab" data-bs-target="#nav-materi" type="button" role="tab" aria-controls="nav-materi" aria-selected="false">Materi Terbanyak</button>
                        <button class="nav-link" id="nav-standing-tab" data-bs-toggle="tab" data-bs-target="#nav-standing" type="button"> Standing Kelas</button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-usia" role="tabpanel" aria-labelledby="nav-usia-tab">
                        <div class="chart-container" style="position: relative; height: auto;">
                            <canvas id="UsiaChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-lokasi" role="tabpanel" aria-labelledby="nav-lokasi-tab">
                        <div class="chart-container" style="position: relative; height: auto;">
                            <canvas id="LokasiChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-materi" role="tabpanel" aria-labelledby="nav-materi-tab">
                        <div class="chart-container" style="position: relative; height: auto;">
                            <canvas id="MateriChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade"
                        id="nav-standing"
                        role="tabpanel">
                        <table id="standingTable"
                        class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Ranking</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Email</th>
                                    <th>Perusahaan</th>
                                    <th>Jumlah Kelas</th>
                                    <th>Daftar Materi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
               
            </div>
        </div>
    </div>
    <div class="row justify-content-center m-1">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Jabatan')
                    <a href="{{ route('jabatan.create') }}" class="btn btn-md click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Jabatan</a>
                @endcan
            </div>
            <div class="card m-2">
                <div class="card-body table-responsive">
                    {{-- <h3 class="card-title text-center my-1">{{ __('Database Klien') }}</h3> --}}
                    <table class="table table-striped" id="dbklienTable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">No</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Jenis Kelamin</th>
                                <th scope="col">Usia</th>
                                <th scope="col">Email</th>
                                <th scope="col">No Handphone</th>
                                <th scope="col">Perusahaan/Instansi</th>
                                <th scope="col">Nama Materi</th>
                                <th scope="col">Nama Sales</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
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

    /* From Uiverse.io by JesusRafaelNavaCruz */ 
    .container-dash {
    overflow: auto;
    display: flex;
    scroll-snap-type: x mandatory;
    width: 90%;
    margin: 0 auto;
    padding: 0 15px;
    }

    .card-dash {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    border-radius: 10px;
    padding: 2rem;
    margin: 1rem;
    width: 100%;
    }

    .title-dash {
    width: 100%;
    display: inline-block;
    word-break: break-all;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    margin: auto;
    }

    
</style>
{{-- @push('js') --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        var tableIndex = 1;
        $('#dbklienTable').DataTable({
            "ajax": {
                "url": "{{ route('getDBKlien') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {   "data": null,
                    "render": function (data){
                        return tableIndex++
                    }
                },
                {"data": "created_at", "visible": false},
                {"data": "nama_formatted"},
                {"data": "jenis_kelamin"},
                {"data": "usia"},
                {"data": "email"},
                {"data": "no_hp"},
                {"data": "nama_perusahaan"},
                {
                    data: 'materi_list',
                    render: function(data) {

                        if (!data || data.length === 0)
                            return '-';

                        let html = '';

                        data.forEach((m,i) => {

                            html += m;

                            if (i !== data.length - 1) {
                                html +=
                                '<hr style="margin:4px 0;">';
                            }

                        });

                        return html;
                    }
                },




                {"data": "sales_key"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if (userRole === 'Direktur' || userRole === 'Direktur Utama') {
                            return "";
                        } else {
                            var actions = "";
                            actions += '@if (auth()->user()->can('Delete Jabatan'))'
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '@can('Delete Jabatan')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/jabatan') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions += '@else';
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '</div>';
                                actions += '@endif';
                            return actions;
                        }
                    }
                }
            ],
            "order": [[1, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs" : [{"targets":[1], "type":"date"}],
        });
        fetchDashboard();
        let usiaRendered = false;
        let lokasiRendered = false;

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).data('bs-target');
            if (target === '#nav-usia' && !usiaRendered) {
                prepareUsiaChart();
                usiaRendered = true;
            }
            if (target === '#nav-lokasi' && !lokasiRendered) {
                prepareLokasiChart();
                lokasiRendered = true;
            }
            if (target === '#nav-materi') {
                prepareMateriChart(); // 🔥 render saat dibuka
            }
            if (target === '#nav-standing') {
                prepareStandingKelas();
            }
        });

    });
    function fetchDashboard() {
        $.ajax({
            url: "{{ route('getDBKlien') }}",
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.data) return;

            globalPeserta = response.data;

            // Default render tab pertama
            prepareUsiaChart();
            prepareLokasiChart();
            prepareMateriChart();
            prepareCardPesertaTahun();
            },

            error: function () {
                $('#UsiaChart').replaceWith('<p>Data tidak tersedia</p>');
                $('#LokasiChart').replaceWith('<p>Data tidak tersedia</p>');
                $('#MateriChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        });
    }

    function prepareLokasiChart() {
        let kelompok = {};
        globalPeserta.forEach(p => {
            let lokasi = p.lokasi ?? 'Tidak Diketahui';
            if (!kelompok[lokasi]) {
                kelompok[lokasi] = 0;
            }
            kelompok[lokasi]++;
        });
        renderLokasiChart(
            Object.keys(kelompok),
            Object.values(kelompok)
        );
    }

    function prepareUsiaChart() {
        let kelompok = {
            '<20': 0,
            '20-25': 0,
            '26-30': 0,
            '31-35': 0,
            '36-40': 0,
            '41-45': 0,
            '46-50': 0,
            '>50': 0
        };
        globalPeserta.forEach(p => {
            let usia = parseInt(p.usia);
            if (!usia) return;
            if (usia < 20) kelompok['<20']++;
            else if (usia <= 25) kelompok['20-25']++;
            else if (usia <= 30) kelompok['26-30']++;
            else if (usia <= 35) kelompok['31-35']++;
            else if (usia <= 40) kelompok['36-40']++;
            else if (usia <= 45) kelompok['41-45']++;
            else if (usia <= 50) kelompok['46-50']++;
            else kelompok['>50']++;

        });
        renderUsiaChart(
            Object.keys(kelompok),
            Object.values(kelompok)
        );
    }

    function prepareMateriChart() {
        let kelompok = {};
        let pesertaMap = {};

        globalPeserta.forEach(p => {

            let materi =
                p.nama_materi
                ?? 'Tidak Diketahui';

            if (!kelompok[materi]) {
                kelompok[materi] = 0;
                pesertaMap[materi] = [];
            }

            kelompok[materi]++;

            pesertaMap[materi].push(p);

        });

        window.materiPesertaMap = pesertaMap;

        renderMateriChart(
            Object.keys(kelompok),
            Object.values(kelompok)
        );
    }


    function prepareStandingKelas() {

        let map = {};

        globalPeserta.forEach(p => {

            let nama = p.nama_formatted ?? p.nama;

            if (!map[nama]) {
                map[nama] = {
                    nama: nama,
                    no_hp: p.no_hp ?? '-',
                    perusahaan: p.nama_perusahaan ?? '-',
                    email: p.email ?? '-',
                    materi: new Set()
                };
            }

            // 🔥 ambil semua materi
            if (p.materi_list && p.materi_list.length > 0) {
                p.materi_list.forEach(m => {
                    map[nama].materi.add(m);
                });
            }

        });

        let data = Object.values(map).map(p => ({
            nama: p.nama,
            no_hp: p.no_hp,
            perusahaan: p.perusahaan,
            email: p.email,
            jumlah_kelas: p.materi.size,
            materi: [...p.materi]
        }));

        data = data.filter(p => p.jumlah_kelas > 1); // exclude 1 kelas

        data.sort((a,b) => b.jumlah_kelas - a.jumlah_kelas);

        renderStandingTable(data);
    }

    function prepareCardPesertaTahun() {
        let kelompok = {};

        globalPeserta.forEach(p => {

            if (!p.created_at) return;

            // Ambil tahun
            let tahun =
                new Date(p.created_at)
                .getFullYear();

            if (!kelompok[tahun]) {
                kelompok[tahun] = 0;
            }

            kelompok[tahun]++;

        });

        renderCardPesertaTahun(kelompok);
    }

    function renderUsiaChart(labels, data) {
        const canvas = document.getElementById('UsiaChart');
        const ctx = canvas.getContext('2d');
        if (window.UsiaChart instanceof Chart) {
            window.UsiaChart.destroy();
        }
        if (window.innerWidth <= 900) {
            canvas.height = 600;
        }
        window.UsiaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Distribusi Usia Peserta',
                    data: data,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'Kelompok Usia'
                        }
                    },
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Peserta'
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (!elements.length) return;
                    const index = elements[0].index;
                    const label = labels[index];
                    drilldownUsia(label);
                }
            }
        });
    }
    
    function renderLokasiChart(labels, data) {
        const canvas = document.getElementById('LokasiChart');
        const ctx = canvas.getContext('2d');
        if (window.LokasiChart instanceof Chart) {
            window.LokasiChart.destroy();
        }
        if (window.innerWidth <= 900) {
            canvas.height = 600;
        }
        window.LokasiChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Distribusi Lokasi Peserta',
                    data: data,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'x',
                responsive: true,
                onClick: function(evt, elements) {
                if (!elements.length) return;
                const index = elements[0].index;
                const label = labels[index];
                drilldownLokasi(label);
            }

            }
        });
    }

    function renderMateriChart(labels, data) {

        const canvas =
            document.getElementById('MateriChart');

        const ctx = canvas.getContext('2d');

        if (window.MateriChart instanceof Chart) {
            window.MateriChart.destroy();
        }

        if (window.innerWidth <= 900) {
            canvas.height = 600;
        }

        window.MateriChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Materi Terbanyak Diikuti',
                    data: data,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'Nama Materi'
                        }
                    },
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Peserta'
                        }
                    }
                },

                // 🔥 Drilldown klik bar
                onClick: function (evt, elements) {

                    if (!elements.length) return;

                    let index = elements[0].index;
                    let materi = labels[index];

                    let detail =
                        window.materiPesertaMap[materi] ?? [];

                    renderDetailPeserta(
                        detail,
                        'Peserta Materi: ' + materi
                    );
                }
            }
        });
    }

    function renderStandingTable(data) {

        if ($.fn.DataTable.isDataTable('#standingTable')) {
            $('#standingTable')
                .DataTable()
                .clear()
                .destroy();
        }

        // 🔥 FILTER: hanya yang jumlah_kelas > 1
        const filtered = data.filter(p => p.jumlah_kelas > 1);

        let html = '';

        filtered.forEach((p, i) => {

            html += `
                <tr>
                    <td>${i+1}</td>
                    <td>${p.nama ?? '-'}</td>
                    <td>${p.no_hp ?? '-'}</td>
                    <td>${p.email ?? '-'}</td>
                    <td>${p.perusahaan ?? '-'}</td>
                    <td>${p.jumlah_kelas ?? 0}</td>
                    <td>
                        ${
                            p.materi?.length
                            ? p.materi.join('<br><hr><br>')
                            : '-'
                        }
                    </td>
                </tr>
            `;
        });

        $('#standingTable tbody').html(html);

        $('#standingTable').DataTable({
            pageLength: 10,
            order: [[5, 'desc']], // kolom jumlah_kelas
            responsive: true,
            language: {
                search: "Cari Peserta:",
                zeroRecords: "Tidak ada data"
            }
        });
    }


    function renderCardPesertaTahun(data) {

        let html = '';

        // Urutkan tahun desc
        let sorted =
            Object.keys(data)
            .sort((a,b) => b - a);

        sorted.forEach(tahun => {

           html += `
            <div class="card-dash"
            onclick="showPesertaByYear(${tahun})"
            style="cursor:pointer">

                <h5>${tahun}</h5>
                <h3 class="title-dash">
                    ${data[tahun]}
                </h3>
                <h6 class="title-dash">
                    Peserta
                </h6>

            </div>`;

        });

        $('#cardPesertaTahun').html(html);
    }


    function drilldownUsia(label) {
        let filtered = globalPeserta.filter(p => {
            let usia = parseInt(p.usia);
            if (!usia) return false;
            switch(label) {
                case '<20': return usia < 20;
                case '20-25': return usia >= 20 && usia <= 25;
                case '26-30': return usia >= 26 && usia <= 30;
                case '31-35': return usia >= 31 && usia <= 35;
                case '36-40': return usia >= 36 && usia <= 40;
                case '41-45': return usia >= 41 && usia <= 45;
                case '46-50': return usia >= 46 && usia <= 50;
                case '>50': return usia > 50;
                default: return false;
            }
        });
        renderDetailPeserta(filtered, 'Kelompok Usia ' + label);
    }

    function drilldownLokasi(lokasi) {

        let filtered =
            globalPeserta.filter(p => {

            return (
                p.lokasi
                ?? 'Tidak Diketahui'
            ) === lokasi;

        });

        renderDetailPeserta(
            filtered,
            'Lokasi ' + lokasi
        );
    }


    function showPesertaByYear(tahun) {

        let filtered =
            globalPeserta.filter(p => {

            if (!p.created_at) return false;

            return new Date(p.created_at)
                .getFullYear() == tahun;
        });

        renderDetailPeserta(
            filtered,
            'Peserta Tahun ' + tahun
        );
    }



    function renderDetailPeserta(data, title) {
        // Set judul modal
        $('#detailPesertaModal .modal-title').text(title);
        let html = '';
        if (data.length === 0) {
            html = `
                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada data
                    </td>
                </tr>`;
        } else {
            data.forEach((p, i) => {
                html += `
                    <tr>
                        <td>${p.nama_formatted ?? p.nama ?? '-'}</td>
                        <td>${p.usia ?? '-'}</td>
                        <td>${p.nama_perusahaan ?? '-'}</td>
                        <td>${p.lokasi ?? '-'}</td>

                    </tr>
                `;
            });
        }

        // Destroy DataTable jika sudah pernah init
        if ($.fn.DataTable.isDataTable('#tablePeserta')) {
            $('#tablePeserta').DataTable().clear().destroy();
        }

        // Inject HTML ke tbody
        $('#detailPesertaBody').html(html);

        // Init DataTable
        $('#tablePeserta').DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            responsive: true,
            autoWidth: false,
            order: [], // default no sorting
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ peserta",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "›",
                    previous: "‹"
                },
                zeroRecords: "Data tidak ditemukan"
            }
        });

        // Tampilkan modal
        new bootstrap.Modal('#detailPesertaModal').show();
    }



</script>
{{-- @endpush --}}
@endsection
