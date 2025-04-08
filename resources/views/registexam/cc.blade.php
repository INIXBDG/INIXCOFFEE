@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Tambah Kartu Kredit') }}</h5>
                    <form method="POST" action="{{ route('exam.storecc', $post->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_peserta" class="col-md-4 col-form-label text-md-start">{{ __('Nama Peserta') }}</label>
                            <div class="col-md-6">
                                <input id="nama_peserta" disabled type="text" placeholder="Masukan Kode Materi" class="form-control @error('nama_peserta') is-invalid @enderror" name="nama_peserta" value="{{ old('nama_peserta', $peserta->nama) }}" autocomplete="nama_peserta" autofocus>
                                @error('nama_peserta')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="cc" class="col-md-4 col-form-label text-md-start">{{ __('Kartu Kredit') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('cc') is-invalid @enderror" name="cc" autocomplete="cc">
                                    <option value="" selected>Pilih Kartu Kredit</option>
                                    @foreach ($ccs as $cc)
                                        <option value="{{ $cc->id }}" {{ old('cc', $post->cc) == $cc->id ? 'selected' : '' }}>
                                            {{ $cc->nama_pemilik }} - {{ $cc->tipe_kartu }} - {{ $cc->bank }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cc')
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
                </div>
            </div>
        </div>
    </div>
</div>
<style>

</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

    <script>
    </script>
@endpush
@endsection
