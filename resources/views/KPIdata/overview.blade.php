@extends('databasekpi.berandaKPI')

@section('contentKPI')
<!-- Font & Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Chart.js + Radial Gauge Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-radial-gauge@3.1.0/dist/chartjs-chart-radial-gauge.min.js"></script>

<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --purple: #8b5cf6;
        --pink: #ec4899;
        --teal: #0d9488;
        --card-bg: #ffffff;
        --text: #1e293b;
        --text-light: #64748b;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        color: var(--text);
    }

    .dashboard-header {
        background: linear-gradient(90deg, var(--primary), #818cf8, #c084fc);
        color: white;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
    }

    .kpi-card {
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        background: var(--card-bg);
        border: 1px solid rgba(0, 0, 0, 0.03);
        height: 100%;
    }

    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(99, 102, 241, 0.25);
    }

    .stat-value {
        font-size: 2.4rem;
        font-weight: 800;
        background: linear-gradient(90deg, var(--primary), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        line-height: 1.1;
    }

    .chart-container {
        position: relative;
        height: 360px;
        margin-top: 20px;
    }

    .speedometer-container {
        height: 300px !important;
        margin: 0 auto;
        width: 300px;
    }

    .sparkline {
        height: 50px !important;
        width: 120px !important;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .employee-row {
        transition: all 0.25s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .employee-row:hover {
        background: rgba(99, 102, 241, 0.03);
        transform: scale(1.005);
    }

    .neon-border {
        position: relative;
    }

    .neon-border::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #6366f1, #ec4899, #10b981, #f59e0b);
        z-index: -1;
        border-radius: 22px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .neon-border:hover::after {
        opacity: 1;
    }

    .department-tag {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="content-wrapper">
    <!-- Header Hero -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1"><i class="fas fa-tachometer-alt me-3"></i> KPI Performance Dashboard</h2>
                <p class="opacity-90">Real-time monitoring • 42 karyawan • Periode: Q2 2024</p>
            </div>
            <div class="text-end">
                <div class="h2 mb-0"><i class="fas fa-sync-alt fa-spin me-2"></i> Live</div>
                <small>Updated just now</small>
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row">
        <!-- SPEEDOMETER (Radial Gauge) -->
        <div class="col-xl-4 col-lg-6 grid-margin stretch-card neon-border">
            <div class="card kpi-card">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-4"><i class="fas fa-gauge-high me-2"></i> Overall Performance</h5>
                    <div class="speedometer-container">
                        <canvas id="speedometerChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <span class="stat-value" id="speed-value">84.7%</span>
                        <p class="text-muted mb-0">Company-wide average</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- On Target -->
        <div class="col-xl-2 col-lg-3 col-md-6 grid-margin stretch-card">
            <div class="card kpi-card bg-gradient-to-br from-emerald-50 to-emerald-100 border-emerald-200">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-emerald-700 fw-bold mb-1">✅ On Target</p>
                            <h3 class="stat-value text-emerald-600" id="on-target">28</h3>
                            <p class="text-emerald-600 mb-0"><i class="fas fa-arrow-trend-up"></i> +5 vs Q1</p>
                        </div>
                        <div class="text-emerald-500" style="font-size: 2.2rem;">
                            <i class="fas fa-medal"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Needs Attention -->
        <div class="col-xl-2 col-lg-3 col-md-6 grid-margin stretch-card">
            <div class="card kpi-card bg-gradient-to-br from-amber-50 to-amber-100 border-amber-200">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-amber-700 fw-bold mb-1">⚠️ Needs Attention</p>
                            <h3 class="stat-value text-amber-600" id="needs-attention">9</h3>
                            <p class="text-amber-600 mb-0">
                                <80% target</p>
                        </div>
                        <div class="text-amber-500" style="font-size: 2.2rem;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- At Risk -->
        <div class="col-xl-2 col-lg-3 col-md-6 grid-margin stretch-card">
            <div class="card kpi-card bg-gradient-to-br from-rose-50 to-rose-100 border-rose-200">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-rose-700 fw-bold mb-1">🔥 At Risk</p>
                            <h3 class="stat-value text-rose-600" id="at-risk">5</h3>
                            <p class="text-rose-600 mb-0">
                                <60% target</p>
                        </div>
                        <div class="text-rose-500" style="font-size: 2.2rem;">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performer -->
        <div class="col-xl-2 col-lg-3 col-md-6 grid-margin stretch-card">
            <div class="card kpi-card bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-200">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-indigo-700 fw-bold mb-1">🏆 Top Performer</p>
                            <h5 class="fw-bold text-indigo-600" id="top-performer">Andi Pratama</h5>
                            <p class="text-indigo-600 mb-0">112.3%</p>
                        </div>
                        <div class="text-indigo-500" style="font-size: 2.2rem;">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Trend Bulanan -->
        <div class="col-xl-8 grid-margin stretch-card">
            <div class="card kpi-card">
                <div class="card-body">
                    <h4 class="card-title"><i class="fas fa-chart-line me-2 text-indigo-600"></i> Monthly Performance Trend</h4>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- By Department -->
        <div class="col-xl-4 grid-margin stretch-card">
            <div class="card kpi-card">
                <div class="card-body">
                    <h4 class="card-title"><i class="fas fa-building-columns me-2 text-purple-600"></i> By Department</h4>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0"><i class="fas fa-users-rectangle me-2 text-blue-600"></i> Employee Performance Details</h4>
                        <small class="text-muted">Top 15 performers</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Departemen</th>
                                    <th>Target</th>
                                    <th>Realisasi</th>
                                    <th>Pencapaian</th>
                                    <th>Tren (7h)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="employee-table-body">
                                <!-- Diisi oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // === DATA DUMMY REALISTIS ===
    const DEPARTMENTS = [{
            name: 'Sales',
            color: '#6366f1',
            bg: 'bg-indigo-100 text-indigo-800'
        },
        {
            name: 'Marketing',
            color: '#ec4899',
            bg: 'bg-pink-100 text-pink-800'
        },
        {
            name: 'IT',
            color: '#0d9488',
            bg: 'bg-teal-100 text-teal-800'
        },
        {
            name: 'HR',
            color: '#f59e0b',
            bg: 'bg-amber-100 text-amber-800'
        },
        {
            name: 'Finance',
            color: '#8b5cf6',
            bg: 'bg-purple-100 text-purple-800'
        },
        {
            name: 'Support',
            color: '#3b82f6',
            bg: 'bg-blue-100 text-blue-800'
        }
    ];

    const NAMES = [
        'Andi Pratama', 'Budi Santoso', 'Citra Dewi', 'Doni Wijaya', 'Eka Putri',
        'Fajar Nugroho', 'Gina Lestari', 'Hendra Maulana', 'Intan Kusuma', 'Joko Susilo',
        'Kiki Ramadhan', 'Lina Marlina', 'Mira Agustina', 'Nanda Putra', 'Oki Saputra',
        'Putri Ayu', 'Raka Pratama', 'Siska Rahayu', 'Toni Setiawan', 'Umi Kalsum',
        'Vina Fitri', 'Wawan Hermawan', 'Xena Putri', 'Yudi Prasetyo', 'Zahra Aulia',
        'Adi Saputra', 'Beta Anggraini', 'Caca Maharani', 'Dedi Kurniawan', 'Elisa Sari',
        'Fira Ningsih', 'Guntur Wibowo', 'Hani Safitri', 'Irfan Hakim', 'Juli Ananda',
        'Krisna Adi', 'Lutfi Rahman', 'Mega Sari', 'Nina Fitriani', 'Opung Siregar'
    ];

    function generateRealisticData() {
        const employees = [];
        let topPerformer = {
            name: '',
            pencapaian: 0
        };

        for (let i = 0; i < 42; i++) {
            const dept = DEPARTMENTS[Math.floor(Math.random() * DEPARTMENTS.length)];
            const name = NAMES[i] || `Karyawan ${i+1}`;

            // Target realistic: Sales/Marketing lebih tinggi
            let baseTarget = 50_000_000;
            if (dept.name === 'Sales') baseTarget = 120_000_000;
            else if (dept.name === 'Marketing') baseTarget = 90_000_000;
            else if (dept.name === 'Finance') baseTarget = 70_000_000;

            const target = Math.floor(baseTarget * (0.8 + Math.random() * 0.6)); // ±30%
            const achievementRate = 0.4 + Math.random() * 0.8; // 40% - 120%
            const realisasi = Math.floor(target * achievementRate);
            const pencapaian = Math.min(120, Math.round((realisasi / target) * 1000) / 10);

            if (pencapaian > topPerformer.pencapaian) {
                topPerformer = {
                    name,
                    pencapaian
                };
            }

            // Generate 7-day trend
            const trend = [];
            let current = pencapaian;
            for (let d = 0; d < 7; d++) {
                current += (Math.random() - 0.5) * 6;
                trend.push(Math.max(0, Math.min(120, current)));
            }

            employees.push({
                name,
                dept: dept.name,
                deptColor: dept.color,
                deptBg: dept.bg,
                target,
                realisasi,
                pencapaian,
                trend
            });
        }

        return {
            employees,
            topPerformer
        };
    }

    // === RENDER DASHBOARD ===
    let speedometerChart, trendChart, deptChart;

    function renderDashboard() {
        const {
            employees,
            topPerformer
        } = generateRealisticData();

        // Update stats
        document.getElementById('top-performer').textContent = topPerformer.name;
        document.getElementById('on-target').textContent = employees.filter(e => e.pencapaian >= 90).length;
        document.getElementById('needs-attention').textContent = employees.filter(e => e.pencapaian >= 60 && e.pencapaian < 80).length;
        document.getElementById('at-risk').textContent = employees.filter(e => e.pencapaian < 60).length;

        const overall = employees.reduce((sum, e) => sum + e.pencapaian, 0) / employees.length;
        document.getElementById('speed-value').textContent = overall.toFixed(1) + '%';

        // Render Speedometer
        const speedCtx = document.getElementById('speedometerChart').getContext('2d');
        if (speedometerChart) speedometerChart.destroy();

        speedometerChart = new Chart(speedCtx, {
            type: 'radialGauge',
            {
                labels: ['Performance'],
                [overall]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: false
                        },
                        grid: {
                            circular: true
                        },
                        pointLabels: {
                            display: false
                        },
                        min: 0,
                        max: 120,
                        ticks: {
                            display: false,
                            stepSize: 30
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20
                    }
                }
            }
        });

        // Render Trend Chart
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        const monthlyData = months.map(m => {
            const monthEmp = employees.filter((_, i) => i % 6 === months.indexOf(m));
            return monthEmp.length ? monthEmp.reduce((sum, e) => sum + e.pencapaian, 0) / monthEmp.length : 80;
        });

        const trendCtx = document.getElementById('trendChart').getContext('2d');
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(trendCtx, {
            type: 'bar',
            {
                labels: months,
                datasets: [{
                        label: 'Target Ideal',
                        Array(6).fill(90),
                        borderColor: '#94a3b8',
                        backgroundColor: 'rgba(148, 163, 184, 0.1)',
                        borderWidth: 2,
                        type: 'line',
                        pointRadius: 4,
                        pointBackgroundColor: '#94a3b8',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Rata-rata Pencapaian',
                        monthlyData,
                        backgroundColor: (ctx) => {
                            const value = monthlyData[ctx.dataIndex];
                            return value >= 90 ? 'rgba(16, 185, 129, 0.7)' :
                                value >= 70 ? 'rgba(245, 158, 11, 0.7)' : 'rgba(239, 68, 68, 0.7)';
                        },
                        borderColor: (ctx) => {
                            const value = monthlyData[ctx.dataIndex];
                            return value >= 90 ? '#10b981' :
                                value >= 70 ? '#f59e0b' : '#ef4444';
                        },
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 120,
                        ticks: {
                            callback: v => v + '%'
                        }
                    }
                }
            }
        });

        // Render Department Chart
        const deptAvg = DEPARTMENTS.map(d => {
            const deptEmp = employees.filter(e => e.dept === d.name);
            return deptEmp.length ? deptEmp.reduce((sum, e) => sum + e.pencapaian, 0) / deptEmp.length : 0;
        });

        const deptCtx = document.getElementById('deptChart').getContext('2d');
        if (deptChart) deptChart.destroy();
        deptChart = new Chart(deptCtx, {
            type: 'polarArea',
            {
                labels: DEPARTMENTS.map(d => d.name),
                deptAvg,
                backgroundColor: DEPARTMENTS.map(d => d.color + '80'),
                borderColor: DEPARTMENTS.map(d => d.color),
                borderWidth: 2
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 120
                    }
                }
            }
        });

        // Render Table
        const tbody = document.getElementById('employee-table-body');
        tbody.innerHTML = '';
        [...employees]
        .sort((a, b) => b.pencapaian - a.pencapaian)
            .slice(0, 15)
            .forEach((emp, idx) => {
                const row = document.createElement('tr');
                row.className = 'employee-row';

                let statusText = 'On Target',
                    badgeClass = 'bg-success text-white';
                if (emp.pencapaian < 60) {
                    statusText = 'At Risk';
                    badgeClass = 'bg-danger text-white';
                } else if (emp.pencapaian < 80) {
                    statusText = 'Needs Attention';
                    badgeClass = 'bg-warning text-dark';
                }

                const miniCanvas = document.createElement('canvas');
                miniCanvas.className = 'sparkline';

                row.innerHTML = `
                <td><strong>${idx + 1}</strong></td>
                <td><strong>${emp.name}</strong></td>
                <td><span class="department-tag ${emp.deptBg}">${emp.dept}</span></td>
                <td>Rp${emp.target.toLocaleString('id-ID')}</td>
                <td>Rp${emp.realisasi.toLocaleString('id-ID')}</td>
                <td><strong class="text-primary">${emp.pencapaian}%</strong></td>
                <td><div id="spark-${idx}"></div></td>
                <td><span class="status-badge ${badgeClass}">${statusText}</span></td>
            `;
                tbody.appendChild(row);

                // Sparkline
                new Chart(document.getElementById(`spark-${idx}`).appendChild(miniCanvas), {
                    type: 'line',
                    {
                        labels: Array(7).fill(''),
                        datasets: [{
                            emp.trend,
                            borderColor: badgeClass.includes('success') ? '#10b981' : badgeClass.includes('warning') ? '#f59e0b' : '#ef4444',
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false,
                                min: 0,
                                max: 120
                            }
                        }
                    }
                });
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderDashboard();

        setInterval(renderDashboard, 15000);
    });
</script>
@endsection