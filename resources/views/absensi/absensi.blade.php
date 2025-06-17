@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Modal Spinner -->
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
    <div class="row">
        <div class="col-md-6">
            <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h2>Total Keterlambatan Bulan ini :
                                {{ $totalketerlambatan->total_keterlambatan ?? '0 menit' }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            Data Kehadiran Anda bulan ini
                            <table class="table table-bordered">
                                <thead>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Keterangan</th>
                                    <th>Waktu Keterlambatan</th>
                                </thead>
                                <tbody>
                                    @foreach ($absen as $item)
                                    <tr>
                                        <td>{{\Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y')}}</td>
                                        <td>{{$item->jam_masuk}}</td>
                                        <td>{{$item->jam_keluar}}</td>
                                        <td>{{$item->keterangan}}</td>
                                        <td>{{$item->waktu_keterlambatan}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Ajukan Klaim Absen Anda:</h6>
                            <a href="{{ route('absensi.noRecord') }}" class="btn btn-info color-white">Absen Tidak Terekap</a>
                            <a href="{{ route('absensi.noRecord') }}" class="btn btn-warning">Perubahan Skema Kerja</a>
                            <a href="{{ route('absensi.noRecord') }}" class="btn btn-danger">Pembatalan Cuti</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card my-2">
                <div class="card-body table-responsive">
                    <h4>Leaderboard</h4>
                    <p>Top 3 Karyawan yang terlambat bulan ini :</p>
                    {{-- {{ $topKaryawan->karyawan->foto }} --}}
                    <div class="row justify-content-center">
                        <div class="container profile-container">
                            <div class="row justify-content-center">
                                <!-- Second position on the left -->
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle second-position">
                                        <img src="{{asset('css/b2.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[1]->foto) ? asset('storage/'.$topKaryawan[1]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo">
                                    </div>
                                </div>

                                <!-- First position in the center -->
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle-satu first-position">
                                        <img src="{{asset('css/b1.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[0]->foto) ? asset('storage/'.$topKaryawan[0]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo-satu">
                                    </div>
                                </div>

                                <!-- Third position on the right -->
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle third-position">
                                        <img src="{{asset('css/b3.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[2]->foto) ? asset('storage/'.$topKaryawan[2]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo">

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <p>Karyawan yang terlambat bulan ini:</p>
                    <h5>
                        <table>
                            <tbody>
                                @foreach ($topKaryawan as $item)
                                <tr>
                                    <td>{{$loop->iteration}}.</td>
                                    <td>{{ $item->karyawan->nama_lengkap }} dengan waktu keterlambatan {{$item->total_keterlambatan}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Waktu Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- {{$remainingLeaderboard}} --}}
                            @if($remainingLeaderboard->isNotEmpty())
                            @foreach ($remainingLeaderboard as $item)
                            <tr>
                                <td>{{ $item->karyawan->nama_lengkap }}</td>
                                <td>{{ $item->total_keterlambatan }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada data karyawan yang terlambat</td>
                            </tr>
                            @endif
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

    .container {
        padding: 0;
    }

    .profile-container {
        width: 100%;
        max-width: 700px;
        height: 500px;
        background-size: cover;
        background-position: center;
        background-image: url('/css/podiumkorea.png');
        background-color: #f0f0f0;
        /* Optional background for visual aid */
        margin: 0 auto;
        position: relative;
        overflow-x: auto;
        /* Allow horizontal scrolling when screen is too small */
    }

    /* Circle styles */
    .circle,
    .circle-satu {
        background-color: #6b52cc;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    /* Default sizes */
    .circle {
        width: 150px;
        height: 150px;
    }

    .circle-satu {
        width: 170px;
        height: 170px;
    }

    /* Profile photo adjustments for each circle */
    .profile-photo {
        width: 130px;
        height: 130px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .profile-photo-satu {
        width: 150px;
        height: 150px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Custom positioning for each podium place */
    .second-position {
        position: absolute;
        bottom: 26%;
        left: 9%;
    }

    .first-position {
        position: absolute;
        bottom: 36%;
        left: 51%;
        transform: translateX(-50%);
    }

    .third-position {
        position: absolute;
        bottom: 19%;
        right: 10%;
    }

    /* Position badge */
    .position-badge {
        position: absolute;
        top: -20px;
        right: -58px;
        padding: 5px;
        border-radius: 10px;
        font-size: 0.8rem;
    }

    /* Responsive adjustments for mobile screens */
    @media (max-width: 576px) {
        .profile-container {
            height: 260px;
            width: 360px;
        }

        /* Resize circles for mobile */
        .circle,
        .circle-satu {
            width: 100px;
            height: 100px;
        }

        /* Resize profile photos for mobile */
        .profile-photo {
            width: 80px;
            height: 80px !important;
        }

        .profile-photo-satu {
            width: 90px;
            height: 90px !important;
        }

        /* Adjust positioning for mobile */
        .second-position {
            bottom: 25%;
            left: 7%;
        }

        .first-position {
            bottom: 36%;
            left: 51%;
            transform: translateX(-50%);
        }

        .third-position {
            bottom: 20%;
            right: 6%;
        }

        /* Adjust position badge size for mobile */
        .position-badge {
            top: -10px;
            right: -40px;
            padding: 3px;
            font-size: 0.7rem;
        }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function() {
        //    $('#loadingModal').modal('show')
    });
</script>
@endpush
@endsection