@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Souvenir Khusus Inhouse / Online') }}</h5>
                    <form method="POST" action="{{ route('storeSouvenirInhouse') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="id_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Souvenir') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_rkm" value="{{ $id }}">
                                <select name="nama_souvenir" id="nama_souvenir" class="form-select">
                                    <option value="">-- Pilih Souvenir --</option>
                                    {{-- @foreach ( $souvenir as $s ) --}}
                                    {{-- <option value="{{ $s->id }}">{{ $s->nama_souvenir }}</option> --}}
                                    <option value="All Item">All Item</option>
                                    <option value="Jaket">Jaket</option>
                                    <option value="Diffuser">Diffuser</option>
                                    <option value="Pouch">Pouch</option>
                                    <option value="Tas">Tas</option>
                                    <option value="Kaos">Kaos</option>
                                    <option value="Tumblr">Tumblr</option>
                                    <option value="Botol">Botol</option>
                                    <option value="Polo">Polo</option>
                                    {{-- <option value="Diffuser">Diffuser</option> --}}
                                    {{-- @endforeach --}}
                                </select>
                                @error('nama_souvenir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <label for="nama_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Nama Souvenir') }}</label>
                            <div class="col-md-6">
                                <input id="nama_souvenir" type="text" placeholder="Masukan Nama Souvenir" class="form-control @error('nama_souvenir') is-invalid @enderror" name="nama_souvenir" value="{{ old('nama_souvenir') }}" autocomplete="nama_souvenir" autofocus>
                                @error('nama_souvenir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}

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

<script>

</script>
@endsection
