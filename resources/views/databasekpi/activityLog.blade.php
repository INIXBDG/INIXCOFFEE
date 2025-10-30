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
    </div>
</div>
@endsection