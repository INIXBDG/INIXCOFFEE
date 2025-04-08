@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        {{-- <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a> --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <h5 class="card-title text-center mb-4">{{ __('Registrasi Karyawan Baru') }}</h5>
                    <form method="POST" action="{{ route('user.registkaryawan') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_lengkap" class="col-md-4 col-form-label text-md-start">{{ __('Nama Lengkap') }}</label>
                            <div class="col-md-6">
                                <input id="nama_lengkap" type="text" placeholder="Masukan Nama Lengkap Anda" class="form-control @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required autocomplete="nama_lengkap" autofocus>
                                @error('nama_lengkap')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jabatan" class="col-md-4 col-form-label text-md-start">{{ __('Jabatan') }}</label>
                            <div class="col-md-6">
                                <select id="jabatan" class="form-select @error('jabatan') is-invalid @enderror" name="jabatan" aria-label="Default select example">
                                    <option selected>Pilih Jabatan</option>
                                    @foreach ($jabatan as $jabatans)
                                    <option value="{{ $jabatans->nama_jabatan }}">{{ $jabatans->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                                @error('jabatan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('divisi') is-invalid @enderror" name="divisi" value="{{ old('divisi') }}" required autocomplete="divisi">
                                    <option selected>Pilih Divisi</option>
                                    <option value="Direksi">Direksi</option>
                                    <option value="Education">Education</option>
                                    <option value="Sales & Marketing">Sales & Marketing</option>
                                    <option value="Office">Office</option>
                                    <option value="Service & Operation">Service & Operation</option>
                                </select>
                                @error('divisi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div id="kode_karyawan_row">
                            <div class="row mb-3" id="">
                                <label for="kode_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Kode Karyawan') }}</label>
                                <div class="col-md-6">
                                    <input id="kode_karyawan" type="text" placeholder="Masukan Kode Karyawan" class="form-control @error('kode_karyawan') is-invalid @enderror" name="kode_karyawan" value="{{ old('kode_karyawan') }}" autocomplete="kode_karyawan">
                                    @error('kode_karyawan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="username" class="col-md-4 col-form-label text-md-start">{{ __('Username') }}</label>
                            <div class="col-md-6">
                                <input id="username" type="text" placeholder="Masukan Username Anda" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username">
                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-start">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" placeholder="Masukan Kata Sandi Anda" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-start">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" placeholder="Masukan Kata Sandi Anda Sekali Lagi" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status_akun" class="col-md-4 col-form-label text-md-start">{{ __('Status Akun') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('status_akun') is-invalid @enderror" name="status_akun" aria-label="Default select example">
                                    <option selected>Pilih Status Akun</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                    {{-- <option value="3">Three</option> --}}
                                  </select>
                                @error('status_akun')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <input id="karyawan_id" type="hidden" class="form-control @error('karyawan_id') is-invalid @enderror" name="karyawan_id"  value="{{ $countuser }}" required autocomplete="karyawan_id">
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // document.getElementById('jabatan').addEventListener('change', function() {
    //     var selectedJabatan = this.value;
    //     var kodeKaryawanRow = document.getElementById('kode_karyawan_row');

    //     if (selectedJabatan === 'Instruktur' || selectedJabatan === 'Sales' || selectedJabatan === 'Adm Sales' || selectedJabatan === 'SPV Sales' || selectedJabatan === 'Education Manager') {
    //         kodeKaryawanRow.style.display = 'block';
    //     } else {
    //         kodeKaryawanRow.style.display = 'none';
    //     }
    // });
</script>
@endsection
