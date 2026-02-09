{{-- profil saya --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>


            @php
            use Carbon\Carbon;
            \Carbon\Carbon::setLocale('id');
            @endphp
            @if (auth()->user()->jabatan == "HRD" || auth()->user()->jabatan == "Koordinator Office" || auth()->user()->username == $users->username)
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('karyawan.edit', ['hashid' => $users->hashids]) }}"
                    class="btn btn-md click-primary mx-1">
                    <img src="{{ asset('icon/edit.svg') }}" class="mr-1" width="25px">
                    Edit Profile
                </a>

                <a href="{{ route('user.editPassword', ['hashid' => $users->hashids]) }}" class="btn btn-md click-warning mx-1">
                    <img src="{{ asset('icon/lock.svg') }}" class="mr-1" width="25px">
                    Ganti Password
                </a>

            </div>
            @endif

            {{-- {{ $users }} --}}
            <div class="row m-1">
                {{-- Kolom Kiri --}}
                <div class="col-md-4">
                    {{-- Card Profil --}}
                    <div class="card profile-card mb-3">
                        <div class="card-body text-center profile-card-body">
                            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center mb-3" style="max-width: 100%;">
                                @if ($users->karyawan && $users->karyawan->foto)
                                <div class="text-center">
                                    <img src="{{ asset('storage/posts/'.$users->karyawan->foto) }}" class="rounded shadow-sm" style="width:160px; height:auto; object-fit:cover;">
                                    <div class="mt-2 text-muted small">Foto Profil</div>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center mb-3" style="max-width: 100%;">

                                @if ($users->karyawan && $users->karyawan->ttd)
                                <div class="text-center">
                                    <img src="{{ asset('storage/ttd/'.$users->karyawan->ttd) }}" class="rounded shadow-sm" style="width: 140px; height:auto; object-fit:contain;">
                                    <div class="mt-2 text-muted small">Tanda Tangan</div>
                                </div>
                                @endif
                            </div>
                            <div class="profile-details">
                                <p class="profile-name">{{ Str::title($users->karyawan->nama_lengkap) }}</p>
                                <p class="profile-role">{{ $users->karyawan->jabatan }}</p>
                                <a href="/gantifoto/{{ $users->id }}" class="btn btn-change-photo" data-toggle="tooltip" data-placement="top" title="{{ $users->karyawan && $users->karyawan->foto ? 'Ganti Foto Profil' : 'Tambahkan Foto Profil' }}">
                                    <img src="{{ asset('icon/image.svg') }}" alt="Ganti Foto" width="20px">
                                    <span>{{ $users->karyawan && $users->karyawan->foto ? 'Ganti Foto' : 'Tambah Foto' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Card Posisi --}}
                    <div class="card position-card mb-5">
                        <div class="card-body">
                            <h5 class="card-title">Posisi</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Nomor Induk Pegawai</span>
                                    <span class="detail-value">{{ $users->karyawan->nip }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Jabatan</span>
                                    <span class="detail-value">{{ $users->karyawan->jabatan }}</span>

                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Divisi</span>
                                    <span class="detail-value">{{ $users->karyawan->divisi }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Kode Karyawan</span>
                                    <span class="detail-value">{{ $users->karyawan->kode_karyawan }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Jatah Cuti</span>
                                    <span class="detail-value">{{ $users->karyawan->cuti }} Hari</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(in_array($users->karyawan->jabatan, ['Instruktur', 'Education Manager']))
                        <div class="card certification-card mb-5">
                            <div class="card-body">
                                <h5 class="card-title text-dark fw-bold mb-3">
                                Sertifikasi
                                </h5>

                                @if($sertifikasis->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($sertifikasis as $sertifikat)
                                        @php
                                            // Cek apakah expired
                                            $isExpired = $sertifikat->tanggal_berlaku_sampai && \Carbon\Carbon::parse($sertifikat->tanggal_berlaku_sampai)->endOfDay()->isPast();
                                        @endphp
                                        <div class="list-group-item px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold text-dark">
                                                        {{ $sertifikat->nama_sertifikat }}
                                                    </div>
                                                    <small class="text-muted d-block">{{ $sertifikat->penyedia }}</small>
                                                    <small class="{{ $isExpired ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        Berlaku: {{ \Carbon\Carbon::parse($sertifikat->tanggal_berlaku_dari)->format('d M Y') }}
                                                        @if($sertifikat->tanggal_berlaku_sampai)
                                                        - {{ \Carbon\Carbon::parse($sertifikat->tanggal_berlaku_sampai)->format('d M Y') }}
                                                        @else
                                                        (Seumur Hidup)
                                                        @endif
                                                    </small>
                                                </div>

                                                {{-- Logika Badge Retired/Approved --}}
                                                @if($isExpired)
                                                    <span class="badge bg-secondary">RETIRED</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted small fst-italic mb-0">Belum ada sertifikasi yang disetujui.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                    </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-8">
                    {{-- Card Personal Detail --}}
                    <div class="card personal-detail-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Personal Detail</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Nama Lengkap</span>
                                    <span class="detail-value">{{ $users->karyawan->nama_lengkap }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Username</span>
                                    <span class="detail-value">{{ $users->username }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Role</span>
                                    <span class="detail-value">{{ $users->role }}</span>
                                </div>
                                {{-- @if ($karyawan->divisi == 'Sales & Marketing') --}}
                                <div class="detail-row">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value">{{ $users->karyawan->email ?? '-' }}</span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        {{ $users->karyawan->status_aktif == '1' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Rekening --}}
                    @if ($users->karyawan->rekening_bca || $users->karyawan->rekening_maybank)
                    <div class="card account-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Rekening</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Maybank</span>
                                    <span class="detail-value">{{ $users->karyawan->rekening_maybank ?? '-' }}</span>
                                </div>
                                @if ($users->karyawan->rekening_bca)
                                <div class="detail-row">
                                    <span class="detail-label">BCA</span>
                                    <span class="detail-value">{{ $users->karyawan->rekening_bca }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Card Status Karyawan --}}
                    @if ($users->karyawan->awal_probation || $users->karyawan->awal_kontrak || $users->karyawan->awal_tetap)
                    <div class="card employee-status-card mb-3">
                        <div class="card-body">
                            @if ($users->karyawan->awal_probation)
                            <h5 class="card-title">Probation</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Mulai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->awal_probation)->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Sampai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->akhir_probation)->translatedFormat('d F Y') }}</span>
                                </div>
                            </div>
                            @endif

                            @if ($users->karyawan->awal_kontrak)
                            @if ($users->karyawan->awal_probation)
                            <hr class="section-separator">
                            @endif
                            <h5 class="card-title">Kontrak</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Mulai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->awal_kontrak)->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Sampai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->akhir_kontrak)->translatedFormat('d F Y') }}</span>
                                </div>
                            </div>
                            @endif

                            @if ($users->karyawan->awal_tetap)
                            @if ($users->karyawan->awal_probation || $users->karyawan->awal_kontrak)
                            <hr class="section-separator">
                            @endif
                            <h5 class="card-title">Tetap</h5>
                            <div class="detail-list">
                                <div class="detail-row">
                                    <span class="detail-label">Mulai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->awal_tetap)->translatedFormat('d F Y') }}</span>

                                </div>
                                @if ($users->karyawan->akhir_tetap)
                                <div class="detail-row">
                                    <span class="detail-label">Sampai Tanggal</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($users->karyawan->akhir_tetap)->translatedFormat('d F Y') }}</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if ($users->karyawan->educations && $users->karyawan->educations->count() > 0)
                    <div class="card education-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Latar Belakang Pendidikan</h5>
                            <div class="detail-list">
                                {{-- Gunakan align-items-start agar label tetap di posisi atas --}}
                                <div class="detail-row" style="align-items: flex-start;">

                                    {{-- Kolom Kiri: Label (Hanya 1 kali) --}}
                                    <span class="detail-label">Pendidikan</span>

                                    {{-- Kolom Kanan: Loop Data Sekolah --}}
                                    <span class="detail-value text-end">
                                        @foreach ($users->karyawan->educations as $education)
                                            {{-- Item sekolah --}}
                                            <div class="{{ !$loop->last ? 'mb-2 pb-2' : '' }}"
                                                 style="{{ !$loop->last ? 'border-bottom: 1px dashed #e9ecef;' : '' }}">
                                                {{ $education->name }}
                                            </div>
                                        @endforeach
                                    </span>

                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="visitMonitoring-tab" data-bs-toggle="tab" data-bs-target="#visitMonitoring" type="button" role="tab">
                                        Visit Monitoring
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="loginMonitoring-tab" data-bs-toggle="tab" data-bs-target="#loginMonitoring" type="button" role="tab">
                                        Authentication Monitoring
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="attendanceMonitoring-tab" data-bs-toggle="tab" data-bs-target="#attendanceMonitoring" type="button" role="tab">
                                        Attendance Monitoring
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-2" id="myTabContent">
                                <div class="tab-pane fade show active" id="visitMonitoring" role="tabpanel">
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

                                <div class="tab-pane fade mt-2" id="loginMonitoring" role="tabpanel">
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
                                                    <th>URL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($dataAuth as $auth)
                                                <tr>
                                                    <td>{{ $auth->karyawan->nama_lengkap }}</td>
                                                    <td>{{ $auth->karyawan->jabatan }}</td>
                                                    <td class="{{ $auth->status == 'login' ? 'text-success' : 'text-danger' }}">
                                                        {{ $auth->status }}
                                                    </td>
                                                    <td>{{ Carbon::parse( $auth->created_at)->translatedFormat('l, d F Y H:i') }}</td>
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

                                <div class="tab-pane fade mt-2" id="attendanceMonitoring" role="tabpanel">
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
                                                    <td>{{ Carbon::parse($absen->created_at)->translatedFormat('l, d F Y H:i') }}</td>
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
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
<style>
    /* Atur tata letak kolom untuk layar kecil */
    @media screen and (max-width: 768px) {
        .card {
            padding: 15px;
            max-width: 100%;
        }

        .card-body .row {
            margin-bottom: 10px;
        }

        /* .col-xs-4, */
        .col-xs-1 {
            display: none;
        }

        .col-xs-7 {
            width: 100%;
            text-align: left;
        }
    }

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

    /* body.light-theme #card {
            background-color: #fff;
            color: #000
        }

        body.dark-theme #card {
            background-color: #000;
            color: #fff;
            #
        } */
    .cardname {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .click-secondary-icon {
        background: #355C7C;
        border-radius: 1000px;
        width: 45px;
        height: 45px;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
    }

    .click-secondary-icon i {
        line-height: 45px;
    }

    .click-secondary {
        background: #355C7C;
        border-radius: 5px;
        padding: 5px 10px;
        color: #ffffff;
        display: inline-block;
        font: normal bold 14px/1 "Open Sans", sans-serif;
        text-align: center;
        transition: color 0.1s linear, background-color 0.2s linear;
    }

    .click-secondary:hover {
        color: #A5C7EF;
        transition: color 0.1s linear, background-color 0.2s linear;
    }

    .click-warning {
        background: #f8be00;
        border-radius: 5px;
        padding: 5px 10px;
        color: #000000;
        display: inline-block;
        font: normal bold 14px/1 "Open Sans", sans-serif;
        text-align: center;
        transition: color 0.1s linear, background-color 0.2s linear;/
    }

    .click-warning:hover {
        background: #A5C7EF;
        transition: color 0.1s linear, background-color 0.2s linear;
    }

    .card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: auto;
        height: auto;
        border: qpx solid rgba(255, 255, 255, .25);
        border-radius: 20px;
        background-color: rgba(255, 255, 255, 0.45);
        box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(2px);
        box-shadow: #555 10;
        margin-left: 5%;
        margin-bottom: 10px;
    }

    /* Styling Umum untuk Semua Card */
    .card {
        border-radius: 12px;
        /* Lebih membulat */
        box-shadow: 0 7px 16px rgba(0, 0, 0, 0.45);
        /* Bayangan lebih jelas tapi tetap halus */
        border: none;
        /* Hilangkan border default Bootstrap jika ada */
        overflow: hidden;
        /* Penting untuk border-radius */
        background-color: #fff;
        /* Pastikan background putih */
    }

    .card-body {
        padding: 25px;
        /* Padding yang cukup di dalam card */
    }

    .card-title {
        font-size: 1.25rem;
        /* Ukuran judul card */
        font-weight: 600;
        /* Sedikit lebih tebal */
        color: #333;
        /* Warna teks judul */
        margin-bottom: 20px;
        /* Jarak bawah judul */
    }

    /* Styling untuk Card Profil (kiri atas) */
    .profile-card {
        /* Spesifik jika perlu, tapi sebagian besar sudah di .card */
    }

    .profile-card-body {
        padding-top: 30px;
        /* Lebih banyak padding atas untuk foto */
        padding-bottom: 30px;
    }

    .profile-image-wrapper {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .profile-pic {
        width: 100px;
        /* Ukuran gambar profil */
        height: 100px;
        border-radius: 50%;
        /* Bulat sempurna */
        object-fit: cover;
        /* Pastikan gambar mengisi tanpa distorsi */
        border: 4px solid #f8f9fa;
        /* Border putih halus di sekeliling foto */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Bayangan untuk foto */
    }

    .profile-details {
        margin-top: 15px;
        /* Jarak dari foto ke nama */
    }

    .profile-name {
        font-size: 1.4rem;
        /* Ukuran nama */
        font-weight: 700;
        /* Nama lebih tebal */
        color: #212529;
        /* Warna teks nama */
        margin-bottom: 5px;
        /* Jarak antara nama dan jabatan */
        text-transform: capitalize;
        /* Pastikan kapitalisasi setiap kata */
    }

    .profile-role {
        font-size: 1rem;
        /* Ukuran jabatan */
        color: #6c757d;
        /* Warna abu-abu untuk jabatan */
        margin-bottom: 20px;
        /* Jarak dari jabatan ke tombol */
    }

    .btn-change-photo {
        display: inline-flex;
        /* Agar ikon dan teks sejajar */
        align-items: center;
        gap: 8px;
        /* Jarak antara ikon dan teks */
        background-color: #393E46;
        /* Warna latar belakang tombol yang netral */
        color: #FFFCFB;
        /* Warna teks tombol */
        border: 1px solid #ced4da;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        /* Hilangkan underline default link */
    }

    .btn-change-photo:hover {
        background-color: #dee2e6;
        color: #212529;
        border-color: #adb5bd;
        text-decoration: none;
    }

    /* Styling untuk Card Posisi (kiri bawah) */
    .position-card {
        margin-top: 20px;
        /* Memberi jarak dari card profil di atasnya */
    }

    /* Styling untuk list detail (seperti Posisi, Personal Detail, Rekening) */
    .detail-list {
        margin-top: 0;
        /* Pastikan tidak ada margin atas default */
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        /* Untuk menyelaraskan label kiri, value kanan */
        align-items: baseline;
        /* Menyelaraskan teks secara vertikal */
        margin-bottom: 12px;
        /* Jarak antar baris detail */
        padding-bottom: 5px;
        /* Sedikit padding bawah */
        border-bottom: 1px dashed #e9ecef;
        /* Garis putus-putus untuk pemisah visual */
    }

    .detail-row:last-child {
        margin-bottom: 0;
        /* Hilangkan margin bawah pada baris terakhir */
        border-bottom: none;
        /* Hilangkan garis pada baris terakhir */
    }

    .detail-label {
        font-weight: 500;
        /* Sedikit bold */
        color: #555;
        /* Warna abu-abu untuk label */
        flex-basis: 45%;
        /* Memberi lebar dasar untuk label */
        text-align: left;
        margin-left: 1;
    }

    .detail-value {
        color: #333;
        /* Warna teks value */
        text-align: right;
        flex-basis: 55%;
        /* Memberi lebar dasar untuk value */
        word-wrap: break-word;
        /* Memastikan teks panjang tidak meluber */
    }

    /* Placeholder untuk ikon default jika tidak ada foto */
    .profile-pic[alt="Default Foto Profil"] {
        background-color: #f0f0f0;
        /* Warna background placeholder */
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 3rem;
        /* Ukuran ikon */
        color: #bbb;
        /* Kamu bisa mengganti ini dengan ikon SVG atau font awesome jika ada */
        /* Contoh: content: '👤'; untuk ikon default */
    }

    .background-blur-card {
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        bottom: 10px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        /* transparan putih */
        backdrop-filter: blur(12px);
        /* efek blur halus */
        -webkit-backdrop-filter: blur(12px);
        z-index: 0;
        /* pastikan di belakang card utama */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        /* bayangan tipis */
    }

    .position-relative .card {
        position: relative;
        z-index: 1;
    }
</style>
@endsection
