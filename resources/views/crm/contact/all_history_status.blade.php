@extends('layouts_crm.app')

@section('crm_contents')
    <style>
        /* Cegah pemblokiran render teks (FOIT) */
        h1, h2, h3, h4, h5, h6, p, span, div {
            font-display: swap !important;
        }
    </style>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-4">Analitik & Riwayat Perubahan Status Keseluruhan</h4>
        </div>

    <div class="card mb-4">
            <div class="card-header bg-white fw-bold">Data Riwayat Perubahan Status</div>
            <div class="card-body">
                <!-- Penambahan min-height untuk mencegah elemen terdorong saat data AJAX dimuat -->
                <div class="table-responsive" style="min-height: 450px;">
                    <table class="table table-bordered table-hover w-100" id="historyStatusTable">
                        <thead class="table-primary">
                            <tr>
                                <th style="text-align: center;">No</th>
                                <th style="text-align: center;">Waktu Perubahan</th>
                                <th style="text-align: center;">Nama Perusahaan</th>
                                <th style="text-align: center;">Status Lama</th>
                                <th style="text-align: center;">Status Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables akan mengisi baris asinkron di sini -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata Durasi Konversi Siklus Penjualan</h5>
                        <h2 class="mb-0" id="avgConversionText">Menghitung...</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="card-header bg-white fw-bold">Rasio Transisi Status</div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="transitionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white fw-bold">Tren Volume Aktivitas Perubahan Status</div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mendorong eksekusi JavaScript berat ke Macro-Task Queue
            // Memberikan waktu bagi peramban untuk melukis DOM (LCP) terlebih dahulu
            setTimeout(() => {
                
                // 1. Inisialisasi DataTables
                $('#historyStatusTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    ajax: {
                        url: "{{ route('crm.contact.all_history_status_data') }}",
                        type: "GET"
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: "text-center",
                            orderable: false,
                            searchable: false
                        },
                        { data: 'waktu_perubahan', name: 'waktu_perubahan', className: "text-center" },
                        { data: 'nama_perusahaan', name: 'nama_perusahaan' },
                        { data: 'status_lama', name: 'status_lama', className: "text-center" },
                        { data: 'status_baru', name: 'status_baru', className: "text-center" },
                    ]
                });

                // 2. Fetch Data Analitik Grafik Asinkron
                fetch("{{ route('crm.contact.history_analytics_api') }}")
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('avgConversionText').innerText = `${data.averageConversionDays} Hari`;

                        new Chart(document.getElementById('transitionChart'), {
                            type: 'bar',
                            data: {
                                labels: Object.keys(data.transitionRate),
                                datasets: [{
                                    label: 'Jumlah Transisi',
                                    data: Object.values(data.transitionRate),
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
                        });

                        new Chart(document.getElementById('trendChart'), {
                            type: 'line',
                            data: {
                                labels: Object.keys(data.timeBasedTrends),
                                datasets: [{
                                    label: 'Jumlah Perubahan Status per Hari',
                                    data: Object.values(data.timeBasedTrends),
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                            }
                        });
                    })
                    .catch(error => console.error('Gagal memuat analitik:', error));

            }, 150); // Jeda 150 milidetik untuk melepas hambatan Main Thread
        });
    </script>
@endsection