@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-7 col-sm-7 col-xs-7">
            <div id="card" class="card m-4">
                <div class="card-body table-responsive">
                    <div class="card-title">
                        <a href="{{ url()->previous() }}" class="btn click-primary"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                        <h3 class="text-center my-1">{{ __('Ganti Password') }}</h3>
                        <p class="text-left my-2">Note: jika anda lupa password sebelumnya, maka hubungi HRD!</p>
                    </div>
                    <div class="row">
                        <form action="{{ route('user.updatePassword', $users->id) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Nama Lengkap</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><input id="nama_lengkap" type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap ) }}" required autocomplete="nama_lengkap" disabled>
                                    @error('nama_lengkap')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Username</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $users->username ) }}" required autocomplete="username" disabled>
                                    @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Password Sebelumnya</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><input id="expassword" type="password" class="form-control @error('expassword') is-invalid @enderror" name="expassword" required autocomplete="ex-password">
                                    @error('expassword')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Password</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Konfirmasi Password</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                    @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end my-3">
                                <button type="submit" class="btn click-primary mx-4">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
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

</style>
@endsection
