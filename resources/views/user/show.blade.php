{{-- profil saya --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
            
            @if (auth()->user()->jabatan == "HRD" || auth()->user()->jabatan == "Koordinator Office" || auth()->user()->username == $users->username)
                <div class="d-flex justify-content-end">
                    <a href="/karyawan/{{ $users->hashid }}/edit" class="btn btn-md click-primary mx-1">
                        <img src="{{ asset('icon/edit.svg') }}" class="mr-1" width="25px">
                        Edit Profile
                    </a>
                    <a href="/user/{{ $users->hashid }}/password" class="btn btn-md click-warning mx-1">
                        <img src="{{ asset('icon/lock.svg') }}" class="mr-1" width="25px">
                        Ganti Password
                    </a>
                </div>
            @endif

            {{-- {{ $users }} --}}
            <div class="card-group m-1">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card m-2 align-self-center">
                            <div class="card-body text-center" id="card">
                                <div class="" style="max-width: 500px; max-height:500px">
                                @if ($users->karyawan && $users->karyawan->foto)
                                    <img src="{{ asset('storage/posts/'.$users->karyawan->foto) }}" class="rounded" style="width:200px;height:auto;">
                                @endif
                                @if ($users->karyawan && $users->karyawan->ttd)
                                    <img src="{{ asset('storage/ttd/'.$users->karyawan->ttd) }}" class="rounded" style="width:200px;height:auto;">
                                @endif
                                </div>
                                <div class="m-4 row cardname text-center">
                                    <p style="text-transform: capitalize;">{{ $users->karyawan->nama_lengkap }}</p>
                                    <p>{{ $users->karyawan->jabatan }}</p>
                                        @if ($users->karyawan->foto)
                                        <a href="/gantifoto/{{ $users->id }}" class="btn btn-md click-secondary-icon" data-toggle="tooltip" data-placement="top" title="Ganti Foto Profil"><img src="{{ asset('icon/image.svg') }}" class="" width="30px"></i>
                                        @else
                                        <a href="/gantifoto/{{ $users->id }}" class="btn btn-md click-secondary-icon" data-toggle="tooltip" data-placement="top" title="Tambahkan Foto Profil"><img src="{{ asset('icon/image.svg') }}" class="" width="30px"></i>
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card m-2">
                            <div class="card-body" id="card">
                                <h5 class="card-title">Posisi</h5>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5"><p>Nomor Induk Pegawai</p></div>
                                    <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->nip }}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5"><p>Jabatan</p></div>
                                    <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->jabatan }}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5"><p>Divisi</p></div>
                                    <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->divisi }}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5"><p>Kode Karyawan</p></div>
                                    <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->kode_karyawan }}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5"><p>Jatah Cuti</p></div>
                                    <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->cuti }} Hari</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7 mx-4">
                    <div class="card shadow-sm" id="card">
                        <div class="card-body " id="card">
                            <div class="card my-1">
                                <div class="card-body" id="card">
                                    <h5 class="card-title">Personal Detail</h5>
                                        <div class="row">
                                            <div class="col-md-5 col-sm-5 col-xs-5"><p>Nama Lengkap</p></div>
                                            <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->nama_lengkap }}</p></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5 col-sm-5 col-xs-5"><p>Username</p></div>
                                            <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->username }}</p></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5 col-sm-5 col-xs-5"><p>Role</p></div>
                                            <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->jabatan }}</p></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5 col-sm-5 col-xs-5"><p>Status</p></div>
                                            <div class="col-md-6 col-sm-6 col-xs-6">
                                                @if ($users->karyawan->status_aktif == '1')
                                                   <p>Aktif</p>
                                                @else
                                                    <p>Tidak Aktif</p>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- <div class="row">
                                            <div class="col-md-5 col-sm-5 col-xs-5"><p>Nomor HP</p></div>
                                            <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->notelp }}</p></div>
                                        </div> --}}
                                </div>
                            </div>
                            @if ($users->karyawan->rekening_bca || $users->karyawan->rekening_maybank)
                            <div class="card my-1">
                                <div class="card-body" id="card">
                                    <h5 class="card-title">Rekening</h5>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Maybank</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->rekening_maybank }}</p></div>
                                    </div>
                                    @if ($users->karyawan->rekening_bca)
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>BCA</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ $users->karyawan->rekening_bca }}</p></div>
                                    </div>
                                    @endif


                                </div>
                            </div>
                            @endif
                            @if ($users->karyawan->awal_probation || $users->karyawan->awal_kontrak || $users->karyawan->awal_tetap)
                            <div class="card my-1">
                                <div class="card-body" id="card">
                                    @if ($users->karyawan->awal_probation)
                                    <h5 class="card-title">Probation</h5>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Mulai Tanggal</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->awal_probation)->translatedFormat('d F Y') }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Sampai Tanggal</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->akhir_probation)->translatedFormat('d F Y') }}</p></div>
                                    </div>
                                    @endif
                                    @if ($users->karyawan->awal_kontrak)
                                    <h5 class="card-title">Kontrak</h5>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Mulai Tanggal</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->awal_kontrak)->translatedFormat('d F Y') }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Sampai Tanggal</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->akhir_kontrak)->translatedFormat('d F Y') }}</p></div>
                                    </div>
                                    @endif
                                    @if ($users->karyawan->awal_tetap)
                                    <h5 class="card-title">Tetap</h5>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Mulai Tanggal</p></div>
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->awal_tetap)->translatedFormat('d F Y') }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-5"><p>Sampai Tanggal</p></div>
                                        @if ($users->karyawan->akhir_tetap)
                                        <div class="col-md-6 col-sm-6 col-xs-6"><p>{{ \Carbon\Carbon::parse($users->karyawan->akhir_tetap)->translatedFormat('d F Y') }}</p></div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
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

        .card-body  .row {
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
            background:    #355C7C;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center;
            align-items:   center;
            text-align:    center;
            text-decoration: none;
        }
        .click-secondary-icon i {
            line-height: 45px;
        }

        .click-secondary {
            background:    #355C7C;
            border-radius: 5px;
            padding:       5px 10px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 14px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-secondary:hover {
            color:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-warning {
            background:    #f8be00;
            border-radius: 5px;
            padding:       5px 10px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 14px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /
        }

        .click-warning:hover {
            background:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
            }

</style>

@endsection
