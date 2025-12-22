@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body text-center">
                    <div class="loader"></div>
                    <p class="mt-3 text-white fw-bold">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        @if ($dataLaporan)
        <form method="POST" action="{{ route('uodate.laporanInsiden') }}" enctype="multipart/form-data" id="post" class="col-md-10 col-lg-8">
            @csrf
            <div class="card shadow-sm rounded-3">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn btn-primary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="me-2" width="18px"> Kembali
                    </a>

                    <h4 class="card-title text-center mb-5 mt-4 fw-bold">{{ __('Buat Laporan Anda') }}</h4>

                    <input type="hidden" name="id_laporan" value="{{ $dataLaporan->id }}">

                    <div class="row mb-3">
                        <label for="data_pelapor" class="col-md-4 col-form-label text-md-start">{{ __('Nama Pelapor') }}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control @error('nama_pelapor') is-invalid @enderror" value="{{ $dataLaporan->Pelapor->nama_lengkap }}" readonly required>
                            <input type="hidden" name="id_pelapor" value="{{ $dataLaporan->pelapor }}">
                            @error('data_pelapor')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="data_pelapor" class="col-md-4 col-form-label text-md-start">{{ __('Jabatan Pelapor') }}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control @error('jabatan_pelapor') is-invalid @enderror" value="{{ $dataLaporan->Pelapor->jabatan }}" readonly required>
                            @error('data_pelapor')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="Kategori" class="col-md-4 col-form-label text-md-start">{{ __('Kategori Insiden') }}</label>
                        <div class="col-md-6">
                            <select id="kategori" class="form-control @error('kategori') is-invalid @enderror" name="kategori" required>
                                <option disabled selected>Pilih Kategori Insiden</option>
                                <option value="major" {{ $dataLaporan->kategori === 'major' ? 'selected' : '' }}>Major</option>
                                <option value="minor" {{ $dataLaporan->kategori === 'minor' ? 'selected' : '' }}>Minor</option>
                                <option value="moderate" {{ $dataLaporan->kategori === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            </select>
                            @error('Kategori')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="kejadian" class="col-md-4 col-form-label text-md-start">{{ __('Kejadian') }}</label>
                        <div class="col-md-6">
                            <input name="kejadian" id="kejadian" class="form-control @error('kejadian') is-invalid @enderror" placeholder="kejadian..." value="{{ $dataLaporan->kejadian }}" required>
                            @error('kejadian')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="deskripsi" class="col-md-4 col-form-label text-md-start">{{ __('Deskripsi Insiden') }}</label>
                        <div class="col-md-6">
                            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" required>{{ $dataLaporan->deskripsi }}</textarea>
                            @error('deskripsi')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="tanggal_kejadian" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Kejadian') }}</label>
                        <div class="col-md-6">
                            <input id="tanggal_kejadian" type="date"
                                class="form-control @error('tanggal_kejadian') is-invalid @enderror"
                                name="tanggal_kejadian"
                                value="{{ old('tanggal_kejadian', \Carbon\Carbon::parse($dataLaporan->tanggal_kejadian)->format('Y-m-d')) }}" required>
                            @error('tanggal_kejadian')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="waktu_kejadian" class="col-md-4 col-form-label text-md-start">{{ __('Waktu Kejadian') }}</label>
                        <div class="col-md-6">
                            <input id="waktu_kejadian" type="time"
                                class="form-control @error('waktu_kejadian') is-invalid @enderror"
                                name="waktu_kejadian"
                                value="{{ old('waktu_kejadian', \Carbon\Carbon::parse($dataLaporan->waktu_kejadian)->format('H:i')) }}" required> @error('waktu_kejadian')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="lampiran" class="col-md-4 col-form-label text-md-start">{{ __('Lampiran') }}</label>
                        <div class="col-md-6">
                            <input id="lampiran" type="file" class="form-control @error('lampiran') is-invalid @enderror" name="lampiran">

                            @if ($dataLaporan->lampiran)
                            <div class="mt-2">
                                <p>Lampiran saat ini:</p>
                                <img src="{{ Storage::url($dataLaporan->lampiran) }}" alt="Lampiran Lama" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                            @endif

                            @error('lampiran')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="catatan" class="col-md-4 col-form-label text-md-start">{{ __('Catatan') }}</label>
                        <div class="col-md-6">
                            <input id="catatan" type="time"
                                class="form-control @error('catatan') is-invalid @enderror"
                                name="catatan"
                                value="{{ old('catatan', \Carbon\Carbon::parse($dataLaporan->catatan)->format('H:i')) }}" required> @error('catatan')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-10 offset-md-4 mt-3">
                            <button type="submit" class="btn btn-primary px-4">Simpan</button>
                            <button type="reset" class="btn btn-secondary px-4 ms-2">Reset</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
        @endif
    </div>
</div>

<style>
    .loader {
        display: inline-block;
        width: 60px;
        height: 60px;
        border: 6px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #0d6efd;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
