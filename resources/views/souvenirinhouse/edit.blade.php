@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Souvenir Khusus Inhouse / Online') }}</h5>
                    <form method="POST" action="{{ route('updateSouvenirInhouse', $souvenir->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Souvenir') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_rkm" value="{{ $id }}">
                                <select name="nama_souvenir" id="nama_souvenir" class="form-select">
                                    <option value="">-- Pilih Souvenir --</option>
                                    <option value="Jaket" {{ $souvenir->nama_souvenir == 'Jaket' ? 'selected' : '' }}>Jaket</option>
                                    <option value="Diffuser" {{ $souvenir->nama_souvenir == 'Diffuser' ? 'selected' : '' }}>Diffuser</option>
                                    <option value="Pouch" {{ $souvenir->nama_souvenir == 'Pouch' ? 'selected' : '' }}>Pouch</option>
                                    <option value="Tas" {{ $souvenir->nama_souvenir == 'Tas' ? 'selected' : '' }}>Tas</option>
                                    <option value="Kaos" {{ $souvenir->nama_souvenir == 'Kaos' ? 'selected' : '' }}>Kaos</option>
                                    <option value="Tumblr" {{ $souvenir->nama_souvenir == 'Tumblr' ? 'selected' : '' }}>Tumblr</option>
                                    <option value="Botol" {{ $souvenir->nama_souvenir == 'Botol' ? 'selected' : '' }}>Botol</option>
                                    <option value="Polo" {{ $souvenir->nama_souvenir == 'Polo' ? 'selected' : '' }}>Polo</option>
                                </select>
                                @error('nama_souvenir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
