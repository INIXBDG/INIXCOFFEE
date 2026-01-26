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
                    <h5 class="card-title text-center mb-4">{{ __('Edit List Exam') }}</h5>
                    <form method="POST" action="{{ route('listexams.update', $exam->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="provider" class="col-md-4 col-form-label text-md-start">{{ __('Provider') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="provider" id="provider">
                                    <option value="">Pilih Provider</option>
                                    <option value="Pearson Vue" {{ $exam->provider == 'Pearson Vue' ? 'selected' : '' }}>Pearson Vue</option>
                                    <option value="On Vue" {{ $exam->provider == 'On Vue' ? 'selected' : '' }}>On Vue</option>
                                    <option value="PSI" {{ $exam->provider == 'PSI' ? 'selected' : '' }}>PSI</option>
                                </select>
                                @error('provider')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama_exam" class="col-md-4 col-form-label text-md-start">{{ __('Nama Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_exam" id="nama_exam" value="{{ $exam->nama_exam }}">
                                @error('nama_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="kode_exam" id="kode_exam" value="{{ $exam->kode_exam }}">
                                @error('kode_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="vendor" id="vendor">
                                    <option value="">Pilih Vendor</option>
                                    <option value="AWS" {{ $exam->vendor == 'AWS' ? 'selected' : '' }}>AWS</option>
                                    <option value="Cisco" {{ $exam->vendor == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                    <option value="EC-Council" {{ $exam->vendor == 'EC-Council' ? 'selected' : '' }}>EC-Council</option>
                                    <option value="EPI" {{ $exam->vendor == 'EPI' ? 'selected' : '' }}>EPI</option>
                                    <option value="Google" {{ $exam->vendor == 'Google' ? 'selected' : '' }}>Google</option>
                                    <option value="ISACA" {{ $exam->vendor == 'ISACA' ? 'selected' : '' }}>ISACA</option>
                                    <option value="LSP" {{ $exam->vendor == 'LSP' ? 'selected' : '' }}>LSP</option>
                                    <option value="Microsoft" {{ $exam->vendor == 'Microsoft' ? 'selected' : '' }}>Microsoft</option>
                                    <option value="Mikrotik" {{ $exam->vendor == 'Mikrotik' ? 'selected' : '' }}>Mikrotik</option>
                                    <option value="CompTIA" {{ $exam->vendor == 'CompTIA' ? 'selected' : '' }}>CompTIA</option>
                                    <option value="BNSP" {{ $exam->vendor == 'BNSP' ? 'selected' : '' }}>BNSP</option>
                                    <option value="Inixindo Certificate" {{ $exam->vendor == 'Inixindo Certificate' ? 'selected' : '' }}>Inixindo Certificate</option>
                                </select>
                                @error('vendor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga_exam" class="col-md-4 col-form-label text-md-start">{{ __('Harga') }}</label>
                            <div class="col-md-6"> 
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="harga_exam" id="harga_exam" value={{ $exam->harga_exam }}>
                                </div>
                            </div>
                        </div>
                         
                        <div class="row mb-3">
                            <label for="estimasi_durasi_booking" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Durasi Booking') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="estimasi_durasi_booking" id="estimasi_durasi_booking" value={{ $exam->estimasi_durasi_booking }}>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="note" class="col-md-4 col-form-label text-md-start">{{ __('Note/Syarat Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="note" id="note" value={{ $exam->note }}>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
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
@endsection
