@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Notifikasi Baru') }}</h5>
                    <form method="POST" action="{{ route('notif.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="tipe_notifikasi" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Notifikasi') }}</label>
                            <div class="col-md-6">
                                <select id="tipe_notifikasi" class="form-select @error('tipe_notifikasi') is-invalid @enderror" name="tipe_notifikasi" required autocomplete="tipe_notifikasi" autofocus>
                                    <option value="" selected>Pilih Tipe Notifikasi</option>
                                    <option value="Libur">Libur</option>
                                    <option value="Penting">Penting</option>
                                    <!-- Tambahkan opsi lainnya sesuai kebutuhan -->
                                </select>
                                @error('tipe_notifikasi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="isi_notifikasi" class="col-md-4 col-form-label text-md-start">{{ __('Isi Notifikasi') }}</label>
                            <div class="col-md-6">
                                <textarea id="isi_notifikasi" placeholder="Masukkan isi notifikasi" class="form-control @error('isi_notifikasi') is-invalid @enderror" name="isi_notifikasi" required autocomplete="isi_notifikasi" autofocus></textarea>
                                @error('isi_notifikasi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal_awal" id="tanggal_awal">
                                @error('tanggal_awal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal_akhir" id="tanggal_akhir">
                                @error('tanggal_akhir')
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
