@extends('databasekpi.berandaKPI')
@section('contentKPI')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .custom-scroll {
        overflow-x: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .custom-scroll::-webkit-scrollbar {
        display: none;
    }

    .max-table-height {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@php
use Carbon\Carbon;
\Carbon\Carbon::setLocale('id');
@endphp
<div class="content-wrapper">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="visitMonitoring-tab" data-bs-toggle="tab" data-bs-target="#visitMonitoring" type="button" role="tab">
                visit monitoring
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="loginMonitoring-tab" data-bs-toggle="tab" data-bs-target="#loginMonitoring" type="button" role="tab">
                authentication monitoring
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendanceMonitoring-tab" data-bs-toggle="tab" data-bs-target="#attendanceMonitoring" type="button" role="tab">
                attendance monitoring
            </button>
        </li>
        @if (auth()->user()->jabatan === 'Koordinator ITSM')
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="uptimeMonitoring-tab" data-bs-toggle="tab" data-bs-target="#uptimeMonitoring" type="button" role="tab" aria-controls="uptimeMonitoring" aria-selected="false">
                up time monitoring
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="myTabContent">

        <div class="tab-pane fade show active" id="visitMonitoring" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Kunjungan Anda</h4>
                    <p class="card-description">Data dalam tabel ini terekam saat Anda membuka/melakukan sebuah aksi di Inixcoffee.</p>
                    <div class="table-responsive max-table-height">
                        <table class="table table-sm table-striped">
                            <thead class="table-light sticky-header">
                                <tr>
                                    <th>User</th>
                                    <th>Jabatan</th>
                                    <th>URL</th>
                                    <th>Browser</th>
                                    <th>IP</th>
                                    <th>Platform</th>
                                    <th>User Agent</th>
                                    <th>method</th>
                                    <th>Detail</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataVisit as $visit)
                                <tr>
                                    <td>{{ $visit->karyawan->nama_lengkap }}</td>
                                    <td>{{ $visit->karyawan->jabatan }}</td>
                                    <td>
                                        <a href="{{ $visit->url }}" target="_blank" class="text-decoration-none">
                                            {{ Str::limit($visit->url, 255) }}
                                        </a>
                                    </td>
                                    <td>{{ $visit->browser }}</td>
                                    <td>{{ $visit->ip }}</td>
                                    <td>{{ $visit->platform }}</td>

                                    <td>
                                        @php
                                        $shortUA = Str::limit($visit->user_agent, 15, '');
                                        @endphp
                                        {{ $shortUA }}
                                        @if(strlen($visit->user_agent) > 15)
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#uaRow{{ $visit->id }}">...</a>
                                        @endif
                                    </td>

                                    <td>{{ $visit->method }}</td>

                                    <td>
                                        @if ($visit->method === 'GET')
                                        -
                                        @else
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#detailRow{{ $visit->id }}">
                                            Lihat
                                        </a>
                                        @endif
                                    </td>

                                    <td>{{ Carbon::parse($visit->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                                </tr>

                                <tr class="collapse bg-light" id="uaRow{{ $visit->id }}">
                                    <td colspan="10">
                                        <strong>User Agent Lengkap:</strong><br>
                                        <div style="white-space: normal; word-wrap: break-word;">
                                            {{ $visit->user_agent }}
                                        </div>
                                    </td>
                                </tr>

                                <tr class="collapse bg-light" id="detailRow{{ $visit->id }}">
                                    <td colspan="10">
                                        <strong>Detail:</strong><br>
                                        <div style="white-space: pre-wrap;">
                                            {{ $visit->detail }}
                                        </div>
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="loginMonitoring" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Login-Logout Anda</h4>
                    <p class="card-description">Data dalam tabel ini terekam saat Anda login atau logout ke Inixcoffee.</p>
                    <div class="table-responsive max-table-height">
                        <table class="table table-sm table-striped">
                            <thead class="table-light sticky-header">
                                <tr>
                                    <th>User</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Url</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataAuth as $auth)
                                <tr>
                                    <td>{{ $auth->karyawan->nama_lengkap }}</td>
                                    <td>{{ $auth->karyawan->jabatan }}</td>
                                    <td class="text-success">{{ $auth->status }}</td>
                                    <td>{{ Carbon::parse($auth->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                                    <td>
                                        <a href="{{ $auth->url }}" target="_blank" class="text-decoration-none">
                                            {{ Str::limit($auth->url, 255) }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="attendanceMonitoring" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Absen Anda</h4>
                    <p class="card-description">Data dalam tabel ini terekam saat Anda absen di Inixcoffee.</p>
                    <div class="table-responsive max-table-height">
                        <table class="table table-sm table-striped">
                            <thead class="table-light sticky-header">
                                <tr>
                                    <th>User</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                    <th>URL</th>
                                    <th>Browser</th>
                                    <th>IP</th>
                                    <th>Platform</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataAbsen as $absen)
                                <tr>
                                    <td>{{ $absen->karyawan->nama_lengkap }}</td>
                                    <td>{{ $absen->karyawan->jabatan }}</td>
                                    <td>{{ $absen->status }}</td>
                                    <td>
                                        <a href="{{ $absen->url }}" target="_blank" class="text-decoration-none">
                                            {{ Str::limit($absen->url,  255) }}
                                        </a>
                                    </td>
                                    <td>{{ $absen->browser }}</td>
                                    <td>{{ $absen->ip }}</td>
                                    <td>{{ $absen->platform }}</td>
                                    <td>{{ Carbon::parse($visit->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        @if (auth()->user()->jabatan === 'Koordinator ITSM')
        <div class="tab-pane fade" id="uptimeMonitoring" role="tabpanel" aria-labelledby="uptimeMonitoring-tab">
            <div class="nav nav-tabs" role="tablist">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-inixcoffee" type="button" role="tab" aria-controls="nav-inixcoffee" aria-selected="true">Inixcoffee</button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-inixlatte" type="button" role="tab" aria-controls="nav-inixlatte" aria-selected="false">Inixlatte</button>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="nav-inixcoffee" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">uptime monitoring INIXCOFFEE</div>
                            <div class="p-4">
                                <canvas id="uptimeChartInixcoffee" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-inixlatte" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">uptime monitoring INIXLATTE</div>
                            <div class="p-4">
                                <canvas id="uptimeChartInixlatte" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartDataCache = null;

    $(document).ready(function() {
        $.ajax({
            url: "{{ route('activity.log.chart') }}",
            method: "GET",
            dataType: "json",
            success: function(response) {
                if (!response || typeof response !== 'object' || response.error) {
                    console.error("Data tidak valid:", response);
                    return;
                }
                chartDataCache = response;
                renderChartIfNeeded('uptimeChartInixcoffee', 'https://192.168.95.60:8001/');
            },
            error: function(xhr) {
                console.error("AJAX error:", xhr.responseText);
                alert("Gagal memuat data chart: " + xhr.status + " - " + xhr.statusText);
            }
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('data-bs-target');
            if (target === '#nav-inixcoffee') {
                renderChartIfNeeded('uptimeChartInixcoffee', 'https://192.168.95.60:8001/');
            } else if (target === '#nav-inixlatte') {
                renderChartIfNeeded('uptimeChartInixlatte', 'https://192.168.95.60:8002/');
            }
        });
    });

    function renderChartIfNeeded(canvasId, url) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || ctx.chartInstance || !chartDataCache || !chartDataCache[url]) return;

        const data = chartDataCache[url];
        if (!data || !Array.isArray(data.labels) || !Array.isArray(data.response_times) || !Array.isArray(data.statuses)) return;

        const upData = data.statuses.map(s => s === true ? 1 : null);
        const downData = data.statuses.map(s => s === false ? 1 : null);

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Response Time (ms)',
                    data: data.response_times, // ✅ FIX: tambah 'data:'
                    backgroundColor: 'rgba(54,162,235,0.5)',
                    borderColor: 'rgba(54,162,235,1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'UP',
                    type: 'line',
                    data: upData, // ✅ FIX: tambah 'data:'
                    borderColor: 'rgba(40,167,69,1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1'
                }, {
                    label: 'DOWN',
                    type: 'line',
                    data: downData, // ✅ FIX: tambah 'data:'
                    borderColor: 'rgba(220,53,69,1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Status'
                        },
                        ticks: {
                            stepSize: 1,
                            callback: v => v === 1 ? 'UP' : ''
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Waktu'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.label === 'Response Time (ms)') return `Response Time: ${ctx.parsed.y} ms`;
                                if (ctx.dataset.label === 'UP') return 'Status: UP';
                                if (ctx.dataset.label === 'DOWN') return 'Status: DOWN';
                            }
                        }
                    }
                }
            }
        });

        ctx.chartInstance = chart;
    }
</script>
@endsection