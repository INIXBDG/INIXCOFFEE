@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Tambah Credit Card') }}</h5>
                    <form method="POST" action="{{ route('creditcard.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_pemilik" class="col-md-4 col-form-label text-md-start">{{ __('Nama Pemilik') }}</label>
                            <div class="col-md-6">
                                <input id="nama_pemilik" type="text" placeholder="Masukan Nama Pemilik" class="form-control @error('nama_pemilik') is-invalid @enderror" name="nama_pemilik" value="{{ old('nama_pemilik') }}" autocomplete="nama_pemilik" autofocus>
                                @error('nama_pemilik')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="angka_terakhir" class="col-md-4 col-form-label text-md-start">{{ __('4 Angka Terakhir') }}</label>
                            <div class="col-md-6">
                                <input id="angka_terakhir" type="text" placeholder="Masukan 4 Angka Terakhir" class="form-control @error('angka_terakhir') is-invalid @enderror" name="angka_terakhir" value="{{ old('angka_terakhir') }}" autocomplete="angka_terakhir" autofocus>
                                @error('angka_terakhir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="bank" class="col-md-4 col-form-label text-md-start">{{ __('Nama Bank') }}</label>
                            <div class="col-md-6">
                                <input id="bank" type="text" placeholder="Masukan Nama Bank" class="form-control @error('bank') is-invalid @enderror" name="bank" value="{{ old('bank') }}" autocomplete="bank" autofocus>
                                @error('bank')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="tipe_kartu" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Kartu') }}</label>
                            <div class="col-md-6">
                                <select name="tipe_kartu" class="form-select" id="tipe_kartu">
                                    <option value="" selected>Pilih Kartu</option>
                                    <option value="Visa">Visa</option>
                                    <option value="Mastercard">Mastercard</option>
                                    <option value="American Express">American Express</option>
                                    <option value="JBC">JBC</option>
                                </select>
                                @error('tipe_kartu')
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
