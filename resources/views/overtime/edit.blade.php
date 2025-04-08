@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Materi') }}</h5>
                    <form method="POST" action="{{ route('materi.update', $materis->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi" type="text" placeholder="Masukan Nama Materi" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi', $materis->nama_materi) }}" autocomplete="nama_materi" autofocus>
                                @error('nama_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kategori_materi" class="col-md-4 col-form-label text-md-start">{{ __('Kategori Materi') }}</label>
                            <div class="col-md-6">
                                <input id="kategori_materi" type="text" placeholder="Masukan Kategori Materi" class="form-control @error('kategori_materi') is-invalid @enderror" name="kategori_materi" value="{{ old('kategori_materi', $materis->kategori_materi) }}" autocomplete="kategori_materi" autofocus>
                                @error('kategori_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <input id="vendor" type="text" placeholder="Masukan Vendor Materi" class="form-control @error('vendor') is-invalid @enderror" name="vendor" value="{{ old('vendor', $materis->vendor) }}" autocomplete="vendor" autofocus>
                                @error('vendor')
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
