@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    {{-- {{ $post }} --}}
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h6 class="card-title text-center">Upload Hasil Exam</h6>
                    <form action="{{ route('exam.updateHasilUjian', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="nama_peserta" class="col-md-4 col-form-label text-md-start">{{ __('Nama Peserta') }}</label>
                            <div class="col-md-6">
                                <input id="nama_peserta" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('nama_peserta') is-invalid @enderror" value="{{ $peserta->nama }}" name="nama_peserta" value="{{ old('nama_peserta') }}" autocomplete="nama_peserta" disabled>
                                <input type="hidden" name="id_peserta" value="{{ $peserta->id }}">
                                <input type="hidden" name="id_registexam" value="{{ $post->id }}">
                                <input type="hidden" name="id_hasilexam" value="{{ $hasilexam ? $hasilexam->id : '-' }}">
                                @error('nama_peserta')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
{{-- {{$post}} --}}
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" placeholder="Masukan Email Peserta" class="form-control @error('email') is-invalid @enderror" value="{{ $post->email_exam }}" name="email" value="{{ old('email') }}" autocomplete="email" disabled>
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
                                <input id="kode_exam" type="text" placeholder="Masukan Kode Exam" class="form-control @error('kode_exam') is-invalid @enderror" value="{{ $post->kode_exam }}" name="kode_exam" value="{{ old('kode_exam') }}" autocomplete="kode_exam" disabled>
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
                                <input id="tanggal_exam" type="text" placeholder="Masukan Tanggal Exam" class="form-control @error('tanggal_exam') is-invalid @enderror" value="{{ $post->tanggal_exam }}" name="tanggal_exam" value="{{ old('tanggal_exam') }}" autocomplete="tanggal_exam" disabled>
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
                                <input id="hasil" type="text" placeholder="Masukan Nilai Peserta" class="form-control @error('hasil') is-invalid @enderror" value="{{ $hasilexam ? $hasilexam->hasil : '-' }}" name="hasil" value="{{ old('hasil') }}" autocomplete="hasil">
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
                                <select name="keterangan" id="keterangan" class="form-select">
                                    <option value="Lulus">Lulus</option>
                                    <option value="Tidak Lulus">Tidak Lulus</option>
                                </select>
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
                                <input type="file" class="form-control @error('pdf') is-invalid @enderror" name="pdf" accept="application/pdf">
                                @error('hasil')
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

</style>
@endsection
