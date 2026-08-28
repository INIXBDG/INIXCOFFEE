@extends('layouts_crm.app')

@section('crm_contents')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-4">Analitik & Riwayat Perubahan Status Keseluruhan</h4>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-white fw-bold">Data Riwayat Perubahan Status</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="historyStatusTable">
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
                    <h2 class="mb-0">{{ $averageConversionDays }} Hari</h2>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        $('#historyStatusTable').DataTable({
            processing: true,
            serverSide: true,
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

        // Mengambil data JSON dari server
        const transitionData = @json($transitionRate);
        const trendData = @json($timeBasedTrends);

        new Chart(document.getElementById('transitionChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(transitionData),
                datasets: [{
                    label: 'Jumlah Transisi',
                    data: Object.values(transitionData),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false // Menonaktifkan rasio aspek bawaan agar menyesuaikan tinggi div
            }
        });

        // Menginisialisasi Grafik Tren Aktivitas Berdasarkan Waktu (Line Chart)
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: Object.keys(trendData),
                datasets: [{
                    label: 'Jumlah Perubahan Status per Hari',
                    data: Object.values(trendData),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Menonaktifkan rasio aspek bawaan
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endsection
