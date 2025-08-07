@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row d-flex align-items-stretch mb-6">
        </div>
        <div class="row">

            <!-- Category Distribution -->
            <div class="col-md-6 col-lg-4 order-2 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1 me-2">Kategori Perusahaan</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="kategoriChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <!--/ Category Distribution -->

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('kategoriChart').getContext('2d');
        const chartData = @json($chartData);

        const labels = chartData.map(item => item.kategori);
        const data = chartData.map(item => item.persen);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Persentase Kategori',
                    data: data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)', // Teal
                        'rgba(255, 99, 132, 0.8)', // Red
                        'rgba(54, 162, 235, 0.8)', // Blue
                        'rgba(255, 206, 86, 0.8)', // Yellow
                        'rgba(153, 102, 255, 0.8)', // Purple
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
