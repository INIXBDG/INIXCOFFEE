@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    {{-- {{ $post }} --}}
                    {{-- {{ $registexam }} --}}
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h6 class="card-title text-center">Edit Invoice</h6>
                    <form action="{{ route('exam.updateHasilUjian', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="nama_peserta" class="col-md-4 col-form-label text-md-start">{{ __('Nama Peserta') }}</label>
                            <div class="col-md-6">
                                <input id="nama_peserta" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('nama_peserta') is-invalid @enderror" value="{{ $peserta->nama }}" name="nama_peserta" autocomplete="nama_peserta" disabled>
                                <input type="hidden" name="id_peserta" value="{{ $peserta->id }}">
                                <input type="hidden" name="id_registexam" value="{{ $post->id }}">
                                @error('nama_peserta')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" placeholder="Masukan Nama Peserta" class="form-control @error('email') is-invalid @enderror" value="{{ $registexam->email }}" name="email" autocomplete="email" disabled>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <input id="kode_exam" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('kode_exam') is-invalid @enderror" value="{{ $registexam->kode_exam }}" name="kode_exam" autocomplete="kode_exam" disabled>
                                @error('kode_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_exam" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Exam') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_exam" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('tanggal_exam') is-invalid @enderror" value="{{ $registexam->tanggal_exam }}" name="tanggal_exam" autocomplete="tanggal_exam" disabled>
                                @error('tanggal_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="hasil" class="col-md-4 col-form-label text-md-start">{{ __('Nilai Exam') }}</label>
                            <div class="col-md-6">
                                <input id="hasil" type="text" placeholder="Masukan Nilai Peserta" class="form-control @error('hasil') is-invalid @enderror" value="{{ $post->hasil }}" name="hasil" autocomplete="hasil">
                                @error('hasil')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                            <div class="col-md-6">
                                <input id="keterangan" type="text" placeholder="Masukan Nilai Peserta" class="form-control @error('keterangan') is-invalid @enderror" value="{{ $post->keterangan }}" name="keterangan" autocomplete="keterangan">
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="hasil" class="col-md-4 col-form-label text-md-start">{{ __('Hasil dalam PDF') }}</label>
                            <div class="col-md-6">
                                z
                                <input type="file" class="form-control @error('pdf') is-invalid @enderror" name="pdf" accept="application/pdf">
                                @error('pdf')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Custom CSS */
</style>
@endsection
