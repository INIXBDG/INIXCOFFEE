@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Pertanyaan Baru') }}</h5>
                    <form method="POST" action="{{ route('feedback.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="kategori_feedback" class="col-md-4 col-form-label text-md-start">{{ __('Kategori Feedback') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('kategori_feedback') is-invalid @enderror" name="kategori_feedback" value="{{ old('kategori_feedback' ) }}" required autocomplete="kategori_feedback">
                                    <option selected>Pilih Kategori Feedback</option>
                                    <option value="Materi">Materi</option>
                                    <option value="Pelayanan">Pelayanan</option>
                                    <option value="Fasilitas Laboratium">Fasilitas Laboratium</option>
                                    <option value="Instruktur">Instruktur</option>
                                    <option value="Umum">Umum</option>
                                </select>
                                @error('kategori_feedback')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pertanyaan" class="col-md-4 col-form-label text-md-start">{{ __('Pertanyaan') }}</label>
                            <div class="col-md-6">
                                <input id="pertanyaan" type="text" placeholder="Masukan Pertanyaaan" class="form-control @error('pertanyaan') is-invalid @enderror" name="pertanyaan" value="{{ old('pertanyaan') }}" autocomplete="pertanyaan" autofocus>
                                @error('pertanyaan')
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
