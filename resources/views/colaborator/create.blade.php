@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Kolaborasi Baru') }}</h5>
                    
                    <form method="POST" action="{{ route('colaborator.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-3">
                            <label for="nama_partner" class="col-md-4 col-form-label text-md-start">{{ __('Nama Partner') }}</label>
                            <div class="col-md-6">
                                <input id="nama_partner" type="text" placeholder="Masukan Nama Partner / Perusahaan" class="form-control @error('nama_partner') is-invalid @enderror" name="nama_partner" value="{{ old('nama_partner') }}" autocomplete="nama_partner" autofocus>
                                @error('nama_partner')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="title" class="col-md-4 col-form-label text-md-start">{{ __('Judul Kolaborasi') }}</label>
                            <div class="col-md-6">
                                <input id="title" type="text" placeholder="Masukan Judul Kolaborasi" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" autocomplete="title">
                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="type" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Kolaborasi') }}</label>
                            <div class="col-md-6">
                                <input id="type" type="text" placeholder="Contoh: Webinar, Project" class="form-control @error('type') is-invalid @enderror" name="type" value="{{ old('type') }}" autocomplete="type">
                                @error('type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-md-6">
                                <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}">
                                @error('start_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                            <div class="col-md-6">
                                <input id="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}">
                                @error('end_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <input id="status" type="text" placeholder="Contoh: Aktif, Selesai" class="form-control @error('status') is-invalid @enderror" name="status" value="{{ old('status') }}" autocomplete="status">
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="desc" class="col-md-4 col-form-label text-md-start">{{ __('Deskripsi') }}</label>
                            <div class="col-md-6">
                                <textarea id="desc" class="form-control @error('desc') is-invalid @enderror" name="desc" rows="3" placeholder="Masukan Deskripsi">{{ old('desc') }}</textarea>
                                @error('desc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="document_mou" class="col-md-4 col-form-label text-md-start">{{ __('Dokumen MoU (Opsional)') }}</label>
                            <div class="col-md-6">
                                <input id="document_mou" type="file" class="form-control @error('document_mou') is-invalid @enderror" name="document_mou" accept=".pdf,.doc,.docx">
                                @error('document_mou')
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