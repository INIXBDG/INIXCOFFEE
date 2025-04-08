@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h6 class="card-title text-center">Upload Registrasi Form</h6>
                    <form action="{{ route('uploadRegistForm', $rkm->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            {{-- {{ $rkm }} --}}
                            <label for="rkm_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama RKM') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi" readonly type="text" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ $rkm->materi->nama_materi }}" autocomplete="nama_materi" autofocus>
                                @error('rkm_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input id="id_rkm" readonly type="text" class="form-control @error('id_rkm') is-invalid @enderror" name="id_rkm" value="{{ $rkm->perusahaan->nama_perusahaan }}" autocomplete="id_rkm" autofocus>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_awal" type="date" placeholder="tanggal_awal" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" readonly value="{{ old('tanggal_awal', $rkm->tanggal_awal) }}" autocomplete="tanggal_awal" autofocus>
                                @error('tanggal_awal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Akhir') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_akhir" type="date" placeholder="tanggal_akhir" class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir"  readonly value="{{ old('tanggal_akhir', $rkm->tanggal_akhir) }}" autocomplete="tanggal_akhir" autofocus>
                                @error('tanggal_akhir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="registrasi_form" class="col-md-4 col-form-label text-md-start">{{ __('Registrasi Form (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" accept="application/pdf" class="form-control @error('registrasi_form') is-invalid @enderror" name="registrasi_form">
                                @error('registrasi_form')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-center my-3">
                            <button type="submit" class="btn click-primary mx-4">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>

</style>
@endsection
