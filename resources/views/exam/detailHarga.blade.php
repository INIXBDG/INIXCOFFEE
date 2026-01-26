@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Detail Harga Exam') }}</h5>
                    <form>
                        <div class="row mb-3">
                            <label for="provider" class="col-md-4 col-form-label text-md-start">{{ __('Provider') }}</label>
                            <div class="col-md-6">
                                <input disabled id="provider" type="text" class="form-control" name="provider" value="{{ $exam->provider }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <input disabled id="vendor" type="text" class="form-control" name="vendor" value="{{ $exam->vendor }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="nama_exam" class="col-md-4 col-form-label text-md-start">{{ __('Nama Exam') }}</label>
                            <div class="col-md-6">
                                <input disabled id="nama_exam" type="text" class="form-control" name="nama_exam" value="{{ $exam->nama_exam }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <input disabled id="kode_exam" type="text" class="form-control" name="kode_exam" value="{{ $exam->kode_exam }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="harga" class="col-md-4 col-form-label text-md-start">{{ __('Harga') }}</label>
                            <div class="col-md-6">
                                <input disabled id="harga" type="text" class="form-control" name="harga" value="Rp {{ number_format($exam->harga_exam ?? 0, 0, ',', '.') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="last_update" class="col-md-4 col-form-label text-md-start">{{ __('Last Update Harga') }}</label>
                            <div class="col-md-6">
                                <input disabled id="last_update" type="text" class="form-control" name="last_update" value="{{ $exam->updated_at?->format('d/m/Y') ?? '-' }}
">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="estimasi_durasi" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Durasi Booking') }}</label>
                            <div class="col-md-6">
                                <input disabled id="estimasi_durasi" type="text" class="form-control" name="estimasi_durasi" value="{{ $exam->estimasi_durasi_booking }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="note" class="col-md-4 col-form-label text-md-start">{{ __('Notes/Syarat Exam') }}</label>
                            <div class="col-md-6">
                                <input disabled id="note" type="text" class="form-control" name="note" value="{{ $exam->note }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection